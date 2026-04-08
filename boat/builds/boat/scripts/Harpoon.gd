extends RigidBody3D

func _ready():
	# Connect collision detection
	contact_monitor = true
	max_contacts_reported = 1
	body_entered.connect(_on_body_entered)
	
	# Self-destruct timer
	await get_tree().create_timer(4.0).timeout
	queue_free()

func _on_body_entered(body):
	if body.has_method("take_harpoon_damage"):
		body.take_harpoon_damage(20)
		queue_free()
