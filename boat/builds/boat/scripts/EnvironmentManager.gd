extends Node

enum Weather { CLEAR, CLOUDY, FOGGY, RAIN, STORM }

@export var day_length_seconds: float = 60.0
@export var sun_path: NodePath
@export var env_path: NodePath
@export var rain_particles_path: NodePath
@export var ocean_path: NodePath
@export var thunder_path: NodePath

@onready var sun: DirectionalLight3D = get_node(sun_path)
@onready var env: WorldEnvironment = get_node(env_path)
@onready var rain: GPUParticles3D = get_node(rain_particles_path)
@onready var ocean: MeshInstance3D = get_node(ocean_path)
@onready var thunder: AudioStreamPlayer = get_node(thunder_path)

var game_time_string: String = "12:00"
var time_passed: float = 0.0
var current_weather: Weather = Weather.CLEAR
var weather_timer: float = 120.0

# Storm Logic
var lightning_timer: float = 0.0
var thunder_delay: float = 0.0

func _ready():
	_set_weather(Weather.CLEAR)

func _process(delta):
	# 1. Day/Night Cycle
	time_passed += delta
	var day_progress = fmod(time_passed / day_length_seconds, 1.0)
	var sun_angle = day_progress * TAU
	
	var total_minutes = int(day_progress * 24.0 * 60.0)
	var hours = int(total_minutes / 60.0)
	var mins = int(total_minutes % 60.0)
	game_time_string = "%02d:%02d" % [hours, mins]
	
	sun.rotation.x = sun_angle - PI/2.0
	var sun_elevation = -sin(sun_angle)
	
	# 2. Weather Cycle
	weather_timer -= delta
	if weather_timer <= 0:
		_set_weather(Weather.values().pick_random())
		weather_timer = randf_range(120.0, 300.0)
	
	# 3. Update Environment based on Time and Weather
	_update_environment_visuals(sun_elevation, delta)
	
	# 4. Storm Logic
	if current_weather == Weather.STORM:
		_process_storm(delta)

func _set_weather(new_weather: Weather):
	current_weather = new_weather
	print("Weather changed to: ", Weather.keys()[current_weather])
	
	# Update Ocean Shader
	if ocean and ocean.material_override:
		var wave_h = 2.0 # Default from Main.tscn
		if current_weather == Weather.STORM:
			wave_h *= 2.0
		ocean.material_override.set_shader_parameter("wave_height", wave_h)
	
	# Initial particles
	rain.emitting = (current_weather == Weather.RAIN or current_weather == Weather.STORM)

func _update_environment_visuals(elevation: float, delta: float):
	var sky_mat = env.environment.sky.sky_material as ProceduralSkyMaterial
	if not sky_mat: return
	
	# Base lighting energy
	var target_energy = 1.0
	var target_blur = 1.0
	var fog_density = 0.0
	
	match current_weather:
		Weather.CLEAR:
			target_energy = 1.2
			target_blur = 0.5
		Weather.CLOUDY:
			target_energy = 0.7
			target_blur = 5.0
			fog_density = 0.01
		Weather.FOGGY:
			target_energy = 0.5
			fog_density = 0.08
		Weather.RAIN:
			target_energy = 0.4
			fog_density = 0.03
		Weather.STORM:
			target_energy = 0.2
			fog_density = 0.1
	
	# Apply Night adjustments
	if elevation < -0.1: # Night
		target_energy = 0.1
		sky_mat.sky_top_color = Color("0a0a1a")
		sky_mat.sky_horizon_color = Color("1a1a2e")
	elif elevation > 0.1: # Day
		sky_mat.sky_top_color = Color("4a90e2") # Reset to blue if weather permits
		sky_mat.sky_horizon_color = Color("d1e8ff")
	else: # Sunset/Sunrise
		sky_mat.sky_top_color = Color("fd5e53")
		sky_mat.sky_horizon_color = Color("ffac81")

	# Lerp for smoothness
	sun.light_energy = lerp(sun.light_energy, target_energy, delta)
	sun.shadow_blur = lerp(sun.shadow_blur, target_blur, delta)
	
	# Volumetric Fog
	env.environment.volumetric_fog_enabled = (fog_density > 0)
	if env.environment.volumetric_fog_enabled:
		env.environment.volumetric_fog_density = lerp(env.environment.volumetric_fog_density, fog_density, delta)

	# Bioluminescence Intensity (Lerp based on Night)
	var bio = 0.0
	if elevation < 0.2: # Starting sunset to full night
		bio = clamp((0.2 - elevation) * 5.0, 0.0, 5.0)
	
	if ocean and ocean.material_override:
		ocean.material_override.set_shader_parameter("bioluminescence", bio)

	# Toggle Island Fireflies
	var is_night = elevation < 0.1
	var nodes = get_tree().get_nodes_in_group("night_lights")
	for node in nodes:
		if node is GPUParticles3D:
			node.emitting = is_night

	# Rain follows camera
	if rain.emitting:
		var cam = get_viewport().get_camera_3d()
		if cam:
			rain.global_position = cam.global_position + Vector3(0, 15, 0)

func _process_storm(delta: float):
	lightning_timer -= delta
	if lightning_timer <= 0:
		# TRIGGER LIGHTNING
		_trigger_lightning()
		lightning_timer = randf_range(5.0, 15.0)
	
	if thunder_delay > 0:
		thunder_delay -= delta
		if thunder_delay <= 0:
			thunder.play()

func _trigger_lightning():
	# Flash the light
	sun.light_energy = 50.0
	# Delayed thunder
	thunder_delay = randf_range(0.5, 2.0)
	# Screen shake or other effects could go here
