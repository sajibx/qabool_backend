extends Node3D

signal fishing_started
signal fishing_failed(reason)
signal fishing_succeeded

var is_fishing: bool = false
var fish_distance: float = 20.0
var line_tension: float = 0.0
var is_reeling: bool = false

@onready var camera = $Camera3D

func start_fishing():
	is_fishing = true
	fish_distance = randf_range(15.0, 25.0)
	line_tension = 0.0
	camera.make_current()
	fishing_started.emit()

func stop_fishing():
	is_fishing = false
	fishing_succeeded.emit()

func _process(delta):
	if not is_fishing: return
	
	# Handle Tension and Distance
	if is_reeling:
		# Difficulty scales as it gets closer
		var difficulty = 1.0 + (1.0 - fish_distance / 25.0)
		line_tension += 25.0 * difficulty * delta
		fish_distance -= 3.0 * delta
	else:
		line_tension -= 40.0 * delta
		fish_distance += 1.5 * delta # Fish pulls away
	
	line_tension = clamp(line_tension, 0.0, 100.0)
	fish_distance = clamp(fish_distance, 0.0, 30.0)
	
	# Check Fail
	if line_tension >= 100.0:
		is_fishing = false
		fishing_failed.emit("Line Broke!")
		
	# Check Success
	if fish_distance <= 0.1:
		is_fishing = false
		GameManager.catch_fish()
		fishing_succeeded.emit()
