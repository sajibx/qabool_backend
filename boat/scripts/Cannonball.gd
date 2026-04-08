extends RigidBody3D

func _ready():
	# Connect collision detection
	body_entered.connect(_on_body_entered)
	
	# Self-destruct timer
	await get_tree().create_timer(5.0).timeout
	queue_free()

func _on_body_entered(body: Node):
	if body.has_method("sink"):
		body.sink()
		queue_free()
