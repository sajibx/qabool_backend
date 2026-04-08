extends RigidBody3D

@export var engine_force_val: float = 15.0
@export var turn_torque: float = 3.0

var wave_speed = 1.0
var wave_height = 1.2
var wave_frequency = 0.5

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

func _physics_process(delta):
	if not is_player_active or is_fishing_mode:
		return

	var thrust = 0.0
	var turn = 0.0
	
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
		thrust = engine_force_val * 0.8
		
	var final_thrust = thrust * GameManager.get_speed_multiplier()
	
	apply_central_force(-global_transform.basis.z * final_thrust)
	apply_torque(Vector3(0, turn, 0))
	
	if is_player_active:
		if Input.is_action_just_pressed("fire_left"):
			_fire_cannon("LeftCannonSpawn", -global_transform.basis.x)
		if Input.is_action_just_pressed("fire_right"):
			_fire_cannon("RightCannonSpawn", global_transform.basis.x)
	
	# Update Sound & Particles
	var speed = linear_velocity.length()
	var splash = get_node_or_null("SplashSound")
	if splash:
		splash.pitch_scale = clamp(1.0 + (speed * 0.05), 1.0, 2.5)
	var foam = get_node_or_null("FoamParticles")
	if foam:
		foam.emitting = speed > 1.0

func _integrate_forces(state):
	# 4-Point Buoyancy Physics
	var float_force = 15.0
	var water_damp = 0.1
	var points = [
		Vector3(0, 0, -1.5), # Bow
		Vector3(0, 0, 1.5),  # Stern
		Vector3(-0.75, 0, 0), # Left
		Vector3(0.75, 0, 0)   # Right
	]
	
	for p in points:
		var wp = global_transform * p
		var wh = get_wave_height_at(Vector2(wp.x, wp.z))
		var depth = wh - wp.y
		if depth > 0:
			var f = Vector3.UP * depth * float_force
			f += -state.get_velocity_at_local_position(p) * water_damp
			apply_force(f, wp - global_position)

func get_wave_height_at(pos: Vector2) -> float:
	var t = Time.get_ticks_msec() / 1000.0
	var w1 = sin(pos.x * wave_frequency + t * wave_speed)
	var w2 = cos(pos.y * wave_frequency * 0.8 + t * wave_speed * 1.1)
	var w3 = sin((pos.x + pos.y) * wave_frequency * 0.6 - t * wave_speed * 0.9)
	return (w1 + w2 + w3) * wave_height / 3.0

func _fire_cannon(spawn_node: String, direction: Vector3):
	var spawn_point = get_node(spawn_node)
	if not spawn_point: return
	
	var ball = cannonball_scene.instantiate()
	get_tree().current_scene.add_child(ball)
	ball.global_position = spawn_point.global_position
	
	var impulse = 40.0 * GameManager.get_cannon_multiplier()
	ball.apply_central_impulse(direction * impulse)
	
	apply_central_impulse(-direction * 15.0)
	apply_torque_impulse(Vector3(direction.z, 0, -direction.x) * 5.0)

func _on_body_entered(body):
	if body.is_in_group("island"):
		is_near_island = true
