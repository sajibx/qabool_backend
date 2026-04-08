extends Node3D

@export var cycle_time: float = 7.0
var timer: float = 0.0
var current_cam_idx: int = -1

@onready var ship = get_parent()
@onready var main_camera = ship.get_node("SpringArm3D/Camera3D")
@onready var cinematic_cameras = get_children()

func _process(delta):
	if not ship: return
	
	if ship.is_autosail:
		timer -= delta
		if timer <= 0 or current_cam_idx == -1:
			_next_camera()
			timer = cycle_time + randf_range(-2.0, 2.0)
	else:
		if current_cam_idx != -1:
			_reset_to_main()

func _next_camera():
	current_cam_idx = (current_cam_idx + 1) % cinematic_cameras.size()
	for i in range(cinematic_cameras.size()):
		if cinematic_cameras[i] is Camera3D:
			cinematic_cameras[i].current = (i == current_cam_idx)
	print("Cinematic Cam Switched to Index: ", current_cam_idx)

func _reset_to_main():
	current_cam_idx = -1
	main_camera.make_current()
	for cam in cinematic_cameras:
		if cam is Camera3D:
			cam.current = false
	print("Returning to Main 3rd Person View.")
