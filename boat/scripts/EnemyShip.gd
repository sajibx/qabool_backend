extends RigidBody3D

enum State { PATROL, CHASE, SINK }
var current_state = State.PATROL

@export var speed = 15.0
@export var turn_speed = 1.0

var target_player = null
var patrol_timer = 0.0
var random_turn = 0.0

# Buoyancy constants (matching player ship logic)
var wave_height = 2.0
var wave_frequency = 1.0
var time_passed = 0.0

@onready var detection_zone = $DetectionZone
@onready var hull = $Hull
@onready var mast = $Mast
@onready var collider = $CollisionShape3D
@onready var sink_effect = $SinkEffect

func _ready():
	detection_zone.body_entered.connect(_on_player_entered)
	detection_zone.body_exited.connect(_on_player_exited)
	_pick_random_patrol()

func _physics_process(delta):
	if current_state == State.SINK:
		return
		
	time_passed += delta
	_apply_buoyancy()
	
	match current_state:
		State.PATROL:
			_process_patrol(delta)
		State.CHASE:
			_process_chase(delta)

func _apply_buoyancy():
	# Simple clamped wave logic
	var wave_y = sin(global_position.x * wave_frequency + time_passed) * wave_height
	global_position.y = lerp(global_position.y, wave_y, 0.1)
	rotation.x = lerp_angle(rotation.x, 0, 0.05)
	rotation.z = lerp_angle(rotation.z, 0, 0.05)

func _process_patrol(delta):
	patrol_timer -= delta
	if patrol_timer <= 0:
		_pick_random_patrol()
	
	# Move forward
	apply_central_force(-global_transform.basis.z * speed)
	apply_torque(Vector3(0, random_turn * turn_speed, 0))

func _pick_random_patrol():
	patrol_timer = randf_range(3.0, 7.0)
	random_turn = randf_range(-1.0, 1.0) * 10.0

func _process_chase(delta):
	if not target_player:
		current_state = State.PATROL
		return
	
	var dir_to_player = (target_player.global_position - global_position).normalized()
	var angle_to = -global_transform.basis.z.signed_angle_to(dir_to_player, Vector3.UP)
	
	apply_central_force(-global_transform.basis.z * speed * 1.5)
	apply_torque(Vector3(0, angle_to * turn_speed * 15.0, 0))

func _on_player_entered(body):
	if body.is_in_group("player") or body.name == "Ship":
		target_player = body
		current_state = State.CHASE

func _on_player_exited(body):
	if body == target_player:
		target_player = null
		current_state = State.PATROL

func sink():
	if current_state == State.SINK: return
	current_state = State.SINK
	
	# Visuals & Collision
	hull.visible = false
	mast.visible = false
	collider.disabled = true
	
	# Effects
	sink_effect.emitting = true
	
	# Loot Drop
	_spawn_loot()
	
	# Cleanup
	await get_tree().create_timer(2.0).timeout
	queue_free()

func _spawn_loot():
	var treasure_scene = load("res://scenes/TreasureBox.tscn")
	for i in range(3):
		var treasure = treasure_scene.instantiate()
		get_tree().current_scene.add_child(treasure)
		# Spread them out slightly
		var offset = Vector3(randf_range(-2, 2), 0, randf_range(-2, 2))
		treasure.global_position = global_position + offset
		# Hack: Ensure they are reachable on water
		treasure.scale = Vector3(1.5, 1.5, 1.5)
