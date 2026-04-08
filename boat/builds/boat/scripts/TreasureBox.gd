extends Node3D

@onready var area = $Area3D
@onready var particles = $GoldParticles
@onready var mesh_box = $Box
@onready var mesh_lock = $Lock

var collected: bool = false

func _process(_delta):
	if collected: return
	
	if Input.is_action_just_pressed("interact"):
		for body in area.get_overlapping_bodies():
			# Check if the body looks like a player (CharacterBody3D)
			if body is CharacterBody3D:
				_on_collect()
				break

func _on_collect():
	collected = true
	# Hide visuals
	mesh_box.visible = false
	mesh_lock.visible = false
	
	# Mobile Haptics
	Input.vibrate_handheld(200)
	
	# Play effect
	particles.emitting = true
	
	# Update global state
	if GameManager:
		GameManager.collect_treasure()
	
	# Destroy after particles finish
	await get_tree().create_timer(1.2).timeout
	queue_free()
