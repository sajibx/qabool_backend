extends Node

@export var spawn_interval: float = 1200.0 # 20 Minutes
var spawn_timer: float = 0.0

func _process(delta):
	spawn_timer += delta
	if spawn_timer >= spawn_interval:
		spawn_timer = 0.0
		_spawn_merchant()

func _spawn_merchant():
	var ship = get_tree().get_first_node_in_group("player_ship")
	if not ship: return
	
	var m_scene = load("res://scenes/MerchantShip.tscn")
	var m = m_scene.instantiate()
	
	# Spawn 150 units away in a random direction
	var angle = randf() * TAU
	var offset = Vector3(cos(angle), 0, sin(angle)) * 150.0
	m.global_position = ship.global_position + offset
	
	get_tree().current_scene.add_child(m)
	print("A Merchant Ship has appeared nearby!")
