extends TouchScreenButton

@export var joystick_range: float = 100.0
var output_vector: Vector2 = Vector2.ZERO
var dragging: bool = false
var touch_index: int = -1

@onready var handle = $Handle # A Sprite2D child used for visuals

func _input(event):
	if event is InputEventScreenTouch:
		if event.pressed:
			if is_pressed():
				dragging = true
				touch_index = event.index
		elif event.index == touch_index:
			dragging = false
			touch_index = -1
			output_vector = Vector2.ZERO
			handle.position = Vector2.ZERO
	
	if event is InputEventScreenDrag and dragging and event.index == touch_index:
		var center = global_position + (texture_normal.get_size() / 2.0)
		var offset = event.position - center
		offset = offset.limit_length(joystick_range)
		
		# Update visuals
		handle.position = offset
		
		# Normalize output
		output_vector = offset / joystick_range

func get_output() -> Vector2:
	return output_vector
