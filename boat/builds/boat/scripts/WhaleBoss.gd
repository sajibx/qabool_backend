extends Node3D

@export var path_speed: float = 15.0
@export var harpoon_scene: PackedScene = preload("res://scenes/Harpoon.tscn")

var whale_health: float = 100.0
var boat_health: float = 100.0
var is_victory: bool = false
var is_defeat: bool = false

@onready var path_follow_whale = $Path3D/PathFollowWhale
@onready var path_follow_ship = $Path3D/PathFollowShip
@onready var rope_mesh = $RopeMesh
@onready var camera = $Camera3D
@onready var hud = $WhaleHUD

# Input for Harpoon
var is_dragging: bool = false
var drag_start_pos: Vector2 = Vector2.ZERO

func _ready():
	_generate_circular_path()

func _generate_circular_path():
	var curve = Curve3D.new()
	var radius = 200.0
	for i in range(13): # 12 points for a circle
		var angle = (i / 12.0) * TAU
		var p = Vector3(cos(angle) * radius, 0, sin(angle) * radius)
		# Add some curve variation
		p.y += sin(angle * 2.0) * 10.0
		curve.add_point(p)
	$Path3D.curve = curve

func _process(delta):
	if is_victory or is_defeat: return
	
	# 1. Towing Movement
	path_follow_whale.progress += path_speed * delta
	# Ship follows slightly behind
	path_follow_ship.progress = path_follow_whale.progress - 35.0
	
	# 2. Rope Stretching
	_update_rope()
	
	# 3. Camera Focus
	camera.look_at(path_follow_whale.global_position)
	
	# 4. Periodic Attacks (Sample logic)
	if fmod(path_follow_whale.progress, 150.0) < 1.0:
		_on_whale_attack()

func _update_rope():
	var start = path_follow_ship.global_position
	var end = path_follow_whale.global_position
	var diff = end - start
	
	rope_mesh.global_position = start + diff / 2.0
	rope_mesh.look_at(end, Vector3.UP)
	# Scale the cylinder to match the distance
	rope_mesh.scale.z = diff.length()

func _input(event):
	if is_victory or is_defeat: return
	
	if event is InputEventScreenTouch:
		if event.pressed:
			is_dragging = true
			drag_start_pos = event.position
		else:
			is_dragging = false
			_fire_harpoon(event.position)

func _fire_harpoon(end_pos: Vector2):
	var drag_vec = drag_start_pos - end_pos
	if drag_vec.length() < 20.0: return
	
	var impulse_dir = (camera.project_ray_normal(end_pos) + Vector3(0, 0.2, 0)).normalized()
	var impulse_strength = clamp(drag_vec.length() * 0.1, 10.0, 50.0)
	
	var h = harpoon_scene.instantiate()
	get_tree().current_scene.add_child(h)
	h.global_position = path_follow_ship.global_position + Vector3(0, 2, 0)
	h.apply_central_impulse(impulse_dir * impulse_strength)

func take_harpoon_damage(amount):
	whale_health -= amount
	hud.update_whale_health(whale_health)
	if whale_health <= 0:
		_on_victory()

func _on_whale_attack():
	# Chance to damage boat if player doesn't interrupt or brace
	boat_health -= 15.0
	hud.update_boat_health(boat_health)
	if boat_health <= 0:
		_on_defeat()

func _on_victory():
	is_victory = true
	GameManager.add_whale_bone(1)
	hud.show_message("VICTORY! THE LEVIATHAN FALLS.")

func _on_defeat():
	is_defeat = true
	hud.show_message("DEFEAT. THE BOAT WAS CRUSHED.")
