extends CanvasLayer

@onready var treasure_label = $TreasureLabel
@onready var time_label = $TimeLabel
@onready var fish_label = $FishLabel
@onready var fishing_ui = $FishingUI
@onready var tension_bar = $FishingUI/TensionBar
@onready var reel_btn = $FishingUI/ReelButton
@onready var fish_btn = $FishingButton
@onready var sell_btn = $SellFishButton
@onready var autosail_btn = $AutoSailButton
@onready var logbook_btn = $LogbookButton

var current_vista_id = ""
var current_vista_node = null
var sketch_progress = 0.0
var is_sketching = false

func _ready():
	if GameManager:
		GameManager.treasure_collected.connect(_on_treasure_collected)
		GameManager.fish_caught_updated.connect(_on_fish_caught_updated)
	
	fish_btn.pressed.connect(_on_fish_toggle)
	sell_btn.pressed.connect(_on_sell_fish)
	autosail_btn.pressed.connect(_on_autosail_pressed)
	logbook_btn.pressed.connect(_on_logbook_pressed)
	reel_btn.button_down.connect(_on_reel_start)
	reel_btn.button_up.connect(_on_reel_stop)
	
	$SketchUI/SketchButton.button_down.connect(_on_sketch_start)
	$SketchUI/SketchButton.button_up.connect(_on_sketch_stop)
	
	_update_label(GameManager.treasure_count)

func show_sketch_prompt(id, node):
	current_vista_id = id
	current_vista_node = node
	$SketchUI.visible = true
	$SketchUI/SketchProgress.value = 0

func hide_sketch_prompt():
	if not is_sketching:
		$SketchUI.visible = false
		current_vista_id = ""
		current_vista_node = null

func _on_sketch_start():
	is_sketching = true
	# Play sound placeholder
	var audio = AudioStreamPlayer.new()
	audio.stream = load("res://audio/water_splash.ogg")
	audio.pitch_scale = 0.5 # Deeper scratch sound
	audio.name = "SketchSound"
	add_child(audio)
	audio.play()

func _on_sketch_stop():
	is_sketching = false
	sketch_progress = 0.0
	$SketchUI/SketchProgress.value = 0
	if has_node("SketchSound"):
		get_node("SketchSound").queue_free()

func _on_sketch_complete():
	is_sketching = false
	if GameManager:
		GameManager.add_vista_sketch(current_vista_id)
	
	if current_vista_node:
		current_vista_node.complete_vista()
	
	hide_sketch_prompt()
	$SketchUI.visible = false

func set_crosshair_focused(focused: bool):
	var cp = get_node_or_null("Crosshair/Panel")
	if not cp: return
	
	if focused:
		cp.scale = lerp(cp.scale, Vector2(1.5, 1.5), 0.2)
		cp.modulate = Color(1, 1, 0, 1) # Yellow
	else:
		cp.scale = lerp(cp.scale, Vector2(1, 1), 0.2)
		cp.modulate = Color(1, 1, 1, 1) # White

func _on_fish_toggle():
	var ship = get_tree().current_scene.get_node_or_null("Ship")
	if not ship: return
	
	var active = not ship.is_fishing_mode
	ship.toggle_fishing_mode(active)
	fishing_ui.visible = active
	fish_btn.text = "EXIT" if active else "FISH"

func _on_reel_start():
	var ship = get_tree().current_scene.get_node_or_null("Ship")
	if ship and ship.get_node_or_null("FishingMode"):
		ship.get_node("FishingMode").is_reeling = true

func _on_reel_stop():
	var ship = get_tree().current_scene.get_node_or_null("Ship")
	if ship and ship.get_node_or_null("FishingMode"):
		ship.get_node("FishingMode").is_reeling = false

func _on_sell_fish():
	GameManager.sell_all_fish()

func _on_autosail_pressed():
	var ship = get_tree().current_scene.get_node_or_null("Ship")
	if ship:
		ship.toggle_autosail(not ship.is_autosail)

func _on_logbook_pressed():
	var ui = load("res://scenes/LogbookUI.tscn").instantiate()
	add_child(ui)

func _process(delta):
	if is_sketching:
		sketch_progress += delta / 4.0 # 4 Seconds
		$SketchUI/SketchProgress.value = sketch_progress * 100.0
		if sketch_progress >= 1.0:
			_on_sketch_complete()
	
	var env = get_tree().current_scene.get_node_or_null("EnvironmentManager")
	if env:
		time_label.text = env.game_time_string
	
	if Input.is_action_just_pressed("open_upgrade_menu"):
		var menu = get_tree().current_scene.get_node_or_null("UpgradeMenu/Control")
		if menu:
			menu.open()
			
	# Update Tension Bar & Near Island
	var ship = get_tree().current_scene.get_node_or_null("Ship")
	if ship:
		if ship.is_fishing_mode:
			var f_mode = ship.get_node_or_null("FishingMode")
			if f_mode:
				tension_bar.value = f_mode.line_tension
				if not f_mode.is_fishing: # Success or Fail
					_on_fish_toggle()
		
		# Market UI
		sell_btn.visible = ship.is_near_island and GameManager.fish_count > 0

func _on_treasure_collected(count):
	_update_label(count)

func _on_fish_caught_updated(count):
	fish_label.text = "Fish: %d" % count

func _update_label(count):
	treasure_label.text = "Treasure: %d" % count
