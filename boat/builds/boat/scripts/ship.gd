extends RigidBody3D

@export var engine_force_val: float = 15.0
@export var turn_torque: float = 3.0

var wave_speed = 1.5
var wave_height = 2.0
var wave_frequency = 1.0

@onready var camera = $SpringArm3D/Camera3D
@export var cannonball_scene: PackedScene = preload("res://scenes/Cannonball.tscn")

var is_player_active = true
var is_fishing_mode = false
var is_near_island = false
var is_autosail = false
var player_scene = preload("res://scenes/PlayerFPS.tscn")
var last_fire_time: float = 0.0
var player_instance: Node3D = null

func _ready():
	# Detection for encounters
	contact_monitor = true
	max_contacts_reported = 5
	body_entered.connect(_on_body_entered)
	
	camera.make_current()
	linear_damp = 1.0
	angular_damp = 2.0
	
	GameManager.cosmetic_unlocked.connect(func(_name): _update_cosmetics())
	_update_cosmetics()

func _update_cosmetics():
	if "lantern" in GameManager.unlocked_cosmetics:
		var l = get_node_or_null("BowLantern")
		if l: l.visible = true
	if "parrot" in GameManager.unlocked_cosmetics:
		var p = get_node_or_null("PetParrot")
		if p: p.visible = true
	if "crimson_sails" in GameManager.unlocked_cosmetics:
		var m = get_node_or_null("Mast")
		if m:
			# Safely try to apply crimson sails
			var mat = load("res://scenes/Ship.tscn::StandardMaterial3D_crimson")
			if mat:
				m.set_surface_override_material(0, mat)

func _unhandled_input(event):
	if event.is_action_pressed("interact"):
		if is_player_active:
			if get_colliding_bodies().size() > 0:
				_disembark_ship()
		else:
			if player_instance and is_instance_valid(player_instance):
				var dist = global_position.distance_to(player_instance.global_position)
				if dist < 6.0:
					_embark_ship()

func _physics_process(delta):
	if not is_player_active or is_fishing_mode:
		return

	var thrust = 0.0
	var turn = 0.0
	
	# Check HUD
	var hud = get_tree().current_scene.get_node_or_null("MobileHUD")
	var joy_l = Vector2.ZERO
	var joy_r = Vector2.ZERO
	if hud:
		joy_l = hud.get_node("LeftJoystick").get_output()
		joy_r = hud.get_node("RightJoystick").get_output()

	if Input.is_physical_key_pressed(KEY_W) or joy_l.y < -0.1:
		thrust = engine_force_val
		is_autosail = false
	if Input.is_physical_key_pressed(KEY_S) or joy_l.y > 0.1:
		thrust = -engine_force_val
		is_autosail = false
	
	if Input.is_physical_key_pressed(KEY_A) or joy_r.x < -0.1:
		turn = turn_torque
		is_autosail = false
	if Input.is_physical_key_pressed(KEY_D) or joy_r.x > 0.1:
		turn = -turn_torque
		is_autosail = false
		
	if is_autosail:
		thrust = engine_force_val * 0.8 # Cruise speed
		
	var final_thrust = thrust * GameManager.get_speed_multiplier()
	# Check for nearby islands
	is_near_island = false
	var islands = get_tree().get_nodes_in_group("islands")
	for island in islands:
		if global_position.distance_to(island.global_position) < 150.0:
			is_near_island = true
			break
	
	apply_central_force(-global_transform.basis.z * final_thrust)
	apply_torque(Vector3(0, turn, 0))
	
	# Cannon Firing
	if is_player_active:
		if Input.is_action_just_pressed("fire_left"):
			_fire_cannon("LeftCannonSpawn", -global_transform.basis.x)
			is_autosail = false
		if Input.is_action_just_pressed("fire_right"):
			_fire_cannon("RightCannonSpawn", global_transform.basis.x)
			is_autosail = false
	
	# Update Sound Pitch based on velocity
	var speed = linear_velocity.length()
	var splash = get_node_or_null("SplashSound")
	if splash:
		# 1.0 base pitch, increases with speed
		splash.pitch_scale = clamp(1.0 + (speed * 0.05), 1.0, 2.5)
		
	# Foam Particles
	var foam = get_node_or_null("FoamParticles")
	if foam:
		foam.emitting = speed > 1.0
		foam.amount_ratio = clamp(speed / 30.0, 0.0, 1.0)

func _disembark_ship():
	is_player_active = false
	
	SaveManager.save_game()
	
	# Set anchor mode to prevent drifting away while walking
	linear_damp = 20.0
	angular_damp = 20.0
	
	player_instance = player_scene.instantiate()
	get_tree().current_scene.add_child(player_instance)
	
	# Position player slightly above the ship
	player_instance.global_position = global_position + Vector3(0, 2, 0)
	
	var fps_cam = player_instance.get_node("Head/Camera3D")
	if fps_cam:
		fps_cam.make_current()

func _reembark_ship():
	is_player_active = true
	camera.make_current()
	linear_velocity = Vector3.ZERO
	angular_velocity = Vector3.ZERO
	set_physics_process(true)

func toggle_autosail(enabled: bool):
	is_autosail = enabled
	if enabled:
		print("Auto-Sail Activated. Let the ocean guide you.")
	else:
		print("Manual Control Restored.")

func toggle_fishing_mode(active: bool):
	is_fishing_mode = active
	var f_mode = get_node_or_null("FishingMode")
	if not f_mode: return
	
	if active:
		linear_velocity = Vector3.ZERO
		angular_velocity = Vector3.ZERO
		f_mode.start_fishing()
	else:
		camera.make_current()
		# Logic to reset UI handled by MobileHUD

func _fire_cannon(spawn_node: String, direction: Vector3):
	var spawn_point = get_node(spawn_node)
	if not spawn_point: return
	
	last_fire_time = Time.get_ticks_msec() / 1000.0
	
	var ball = cannonball_scene.instantiate()
	get_tree().current_scene.add_child(ball)
	ball.global_position = spawn_point.global_position
	
	# Initial velocity of ship plus firing impulse
	var impulse = 40.0 * GameManager.get_cannon_multiplier()
	ball.linear_velocity = linear_velocity
	ball.apply_central_impulse(direction * impulse)
	
	# Recoil back to ship
	apply_central_impulse(-direction * 15.0)

func get_speed_percentage() -> float:
	# Assuming 30.0 is roughly max speed with upgrades
	return clamp(linear_velocity.length() / 30.0, 0.0, 1.0)

func _on_area_entered(area):
	if area.is_in_group("encounter_triggers"):
		_on_whale_found()

func _on_whale_found():
	if not is_player_active: return
	
	is_player_active = false
	linear_velocity = Vector3.ZERO
	angular_velocity = Vector3.ZERO
	
	# Fade UI
	var hud = get_node_or_null("../MobileHUD")
	if hud:
		var overlay = hud.get_node_or_null("FadeOverlay")
		if overlay:
			var tween = get_tree().create_tween()
			tween.tween_property(overlay, "color:a", 1.0, 2.0)
			await tween.finished
	
	# Transition Scene
	get_tree().change_scene_to_file("res://scenes/WhaleBoss.tscn")

func _embark_ship():
	is_player_active = true
	
	# Remove anchor mode
	linear_damp = 1.0
	angular_damp = 2.0
	
	camera.make_current()
	Input.set_mouse_mode(Input.MOUSE_MODE_VISIBLE)
	
	player_instance.queue_free()
	player_instance = null

func get_wave_height_at(pos: Vector2) -> float:
	var t = Time.get_ticks_msec() / 1000.0
	var wave1 = sin(pos.x * wave_frequency + t * wave_speed)
	var wave2 = cos(pos.y * wave_frequency + t * wave_speed * 1.2)
	var wave3 = sin((pos.x + pos.y) * wave_frequency * 0.7 - t * wave_speed * 0.8)
	return (wave1 + wave2 + wave3) * wave_height / 3.0

func get_wave_normal(pos: Vector2) -> Vector3:
	var e = 0.1
	var h0 = get_wave_height_at(pos)
	var hx = get_wave_height_at(pos + Vector2(e, 0))
	var hz = get_wave_height_at(pos + Vector2(0, e))
	var v1 = Vector3(e, hx - h0, 0)
	var v2 = Vector3(0, hz - h0, e)
	return v2.cross(v1).normalized()

func _integrate_forces(state):
	var pos_2d = Vector2(state.transform.origin.x, state.transform.origin.z)
	var target_y = get_wave_height_at(pos_2d)
	var normal = get_wave_normal(pos_2d)
	
	var t = state.transform
	t.origin.y = target_y - 0.2
	
	var forward = -t.basis.z
	forward = forward - forward.project(normal)
	forward = forward.normalized()
	if forward.length_squared() < 0.001:
		forward = -t.basis.z
	var right = normal.cross(forward).normalized()
	
	t.basis = Basis(right, normal, -forward)
	state.transform = t
	
	var lv = state.linear_velocity
	lv.y = 0.0
	state.linear_velocity = lv
	
	var av = state.angular_velocity
	av.x = 0.0
	av.z = 0.0
	state.angular_velocity = av

func _on_body_entered(body):
	if body.is_in_group("island"):
		is_near_island = true
