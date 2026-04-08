extends Control

@onready var speed_btn = $Control/Panel/VBoxContainer/SpeedButton
@onready var cannon_btn = $Control/Panel/VBoxContainer/CannonButton
@onready var hull_btn = $Control/Panel/VBoxContainer/HullButton
@onready var harpoon_btn = $Control/Panel/VBoxContainer/HarpoonButton
@onready var close_btn = $Control/Panel/VBoxContainer/CloseButton

func _ready():
	process_mode = PROCESS_MODE_ALWAYS
	visible = false
	
	speed_btn.pressed.connect(_on_upgrade_speed)
	cannon_btn.pressed.connect(_on_upgrade_cannon)
	hull_btn.pressed.connect(_on_upgrade_hull)
	harpoon_btn.pressed.connect(_on_upgrade_harpoon)
	close_btn.pressed.connect(_on_close)

func open():
	visible = true
	get_tree().paused = true
	_update_ui()

func _on_close():
	visible = false
	get_tree().paused = false

func _on_upgrade_speed():
	if GameManager.spend_treasure(5):
		GameManager.speed_level += 1
		_update_ui()

func _on_upgrade_cannon():
	if GameManager.spend_treasure(10):
		GameManager.cannon_level += 1
		_update_ui()

func _on_upgrade_hull():
	if GameManager.spend_whale_bone(1):
		GameManager.hull_armor_level += 1
		_update_ui()

func _on_upgrade_harpoon():
	if GameManager.spend_whale_bone(1):
		GameManager.harpoon_damage_level += 1
		_update_ui()

func _update_ui():
	$Control/Panel/Label.text = "UPGRADES (Bones: %d)" % GameManager.whale_bone
	speed_btn.text = "Upgrade Speed (Lvl %d) - Cost: 5" % GameManager.speed_level
	cannon_btn.text = "Upgrade Cannons (Lvl %d) - Cost: 10" % GameManager.cannon_level
	hull_btn.text = "Hull Reinforcement (Lvl %d) - Cost: 1 Bone" % GameManager.hull_armor_level
	harpoon_btn.text = "Titanium Harpoons (Lvl %d) - Cost: 1 Bone" % GameManager.harpoon_damage_level
