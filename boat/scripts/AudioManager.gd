extends Node

@export var day_music_path: NodePath
@export var night_music_path: NodePath
@export var shanty_music_path: NodePath

@onready var day_player: AudioStreamPlayer = get_node(day_music_path)
@onready var night_player: AudioStreamPlayer = get_node(night_music_path)
@onready var shanty_player: AudioStreamPlayer = get_node(shanty_music_path)

var shanty_timer: float = 0.0
var shanty_active: bool = false

func _ready():
	day_player.play()
	night_player.play()
	shanty_player.play()
	# Start night and shanty muted
	night_player.volume_db = -80.0
	shanty_player.volume_db = -80.0

func _process(_delta):
	var env = get_tree().current_scene.get_node_or_null("EnvironmentManager")
	if not env: return
	
	# Get elevation from EnvironmentManager
	# We'll calculate it similarly if not exposed, but let's assume we can get it or the time progress.
	# Actually, EnvironmentManager calculates elevation. Let's make it a public variable.
	
	var day_progress = fmod(env.time_passed / env.day_length_seconds, 1.0)
	var sun_angle = day_progress * TAU
	var elevation = -sin(sun_angle)
	
	_check_shanty_conditions(_delta)
	_update_fading(elevation)

func _check_shanty_conditions(delta):
	var ship = get_tree().current_scene.get_node_or_null("Ship")
	if not ship or not ship.is_player_active:
		shanty_active = false
		shanty_timer = 0.0
		return
	
	var in_combat = (Time.get_ticks_msec() / 1000.0 - ship.last_fire_time) < 10.0
	# Optional: Check for nearby enemies
	if not in_combat:
		for enemy in get_tree().get_nodes_in_group("enemies"):
			if enemy.global_position.distance_to(ship.global_position) < 200.0:
				in_combat = true
				break
	
	var is_steady = ship.get_speed_percentage() > 0.8 and not ship.is_turning_sharply()
	
	if is_steady and not in_combat:
		shanty_timer += delta
		if shanty_timer >= 5.0:
			shanty_active = true
	else:
		shanty_timer = 0.0
		shanty_active = false

func _update_fading(elevation: float):
	# elevation > 0 is Day, elevation < 0 is Night
	var day_night_factor = clamp((elevation + 0.2) / 0.4, 0.0, 1.0)
	
	# Current BGM Target Volumes (Day vs Night)
	var target_day = lerp(-40.0, 0.0, day_night_factor) if day_night_factor > 0.01 else -80.0
	var target_night = lerp(0.0, -40.0, day_night_factor) if day_night_factor < 0.99 else -80.0
	
	# Shanty Overrides
	if shanty_active:
		day_player.volume_db = lerp(day_player.volume_db, -80.0, 0.05)
		night_player.volume_db = lerp(night_player.volume_db, -80.0, 0.05)
		shanty_player.volume_db = lerp(shanty_player.volume_db, 0.0, 0.05)
	else:
		day_player.volume_db = lerp(day_player.volume_db, target_day, 0.05)
		night_player.volume_db = lerp(night_player.volume_db, target_night, 0.05)
		shanty_player.volume_db = lerp(shanty_player.volume_db, -80.0, 0.05)
