extends Node3D

@export var chunk_size: float = 500.0
@export var render_distance: int = 2 # 5x5 grid
@export var island_size: float = 400.0
@export var island_resolution: int = 30
@export var foliage_density: int = 150
@export var height_multiplier: float = 60.0

var noise: FastNoiseLite
var active_chunks: Dictionary = {} # Vector2i -> Array[Node3D]
var last_player_chunk: Vector2i = Vector2i(-99, -99)
var shared_material: StandardMaterial3D

func _ready():
	noise = FastNoiseLite.new()
	noise.seed = randi() # Continuous seed
	noise.noise_type = FastNoiseLite.TYPE_SIMPLEX
	noise.frequency = 0.01
	
	shared_material = StandardMaterial3D.new()
	shared_material.vertex_color_use_as_albedo = true
	shared_material.roughness = 0.9
	shared_material.specular_mode = StandardMaterial3D.SPECULAR_DISABLED

func set_noise_seed(s: int):
	if not noise: _ready()
	noise.seed = s
	# Clear old chunks to regenerate with new/saved seed
	for c_pos in active_chunks.keys():
		for node in active_chunks[c_pos]:
			node.queue_free()
	active_chunks.clear()
	last_player_chunk = Vector2i(-99, -99)
	print("World seed restored to: ", s)

func _process(_delta):
	var cam = get_viewport().get_camera_3d()
	if not cam: return
	
	var player_pos = cam.global_position
	var p_chunk = Vector2i(floor(player_pos.x / chunk_size), floor(player_pos.z / chunk_size))
	
	if p_chunk != last_player_chunk:
		_update_chunks(p_chunk)
		last_player_chunk = p_chunk

func _update_chunks(p_chunk: Vector2i):
	var new_chunks = []
	for z in range(-render_distance, render_distance + 1):
		for x in range(-render_distance, render_distance + 1):
			new_chunks.append(p_chunk + Vector2i(x, z))
	
	# Add new chunks
	for c_pos in new_chunks:
		if not active_chunks.has(c_pos):
			_generate_chunk(c_pos)
	
	# Remove old chunks
	var to_remove = []
	for c_pos in active_chunks.keys():
		if not c_pos in new_chunks:
			to_remove.append(c_pos)
	
	for c_pos in to_remove:
		for node in active_chunks[c_pos]:
			node.queue_free()
		active_chunks.erase(c_pos)

func _generate_chunk(c_pos: Vector2i):
	var chunk_nodes = []
	# Use chunk coords for deterministic randomness within the chunk
	var chunk_seed = (c_pos.x * 73856093) ^ (c_pos.y * 19349663)
	var rng = RandomNumberGenerator.new()
	rng.seed = chunk_seed
	
	# 1-2 Islands per chunk
	var count = rng.randi_range(1, 2)
	for i in range(count):
		var island = _create_island(c_pos, rng)
		add_child(island)
		chunk_nodes.append(island)
	
	# Occasional Whale Spawn
	if rng.randf() > 0.8:
		var whale = _create_whale_spout(c_pos, rng)
		add_child(whale)
		chunk_nodes.append(whale)
		
	active_chunks[c_pos] = chunk_nodes

func _create_island(c_pos: Vector2i, rng: RandomNumberGenerator) -> MeshInstance3D:
	var offset_x = rng.randf_range(0, chunk_size)
	var offset_z = rng.randf_range(0, chunk_size)
	var pos = Vector3(c_pos.x * chunk_size + offset_x, 0, c_pos.y * chunk_size + offset_z)
	
	var mesh = MeshInstance3D.new()
	mesh.position = pos
	mesh.add_to_group("islands")
	
	var st = SurfaceTool.new()
	st.begin(Mesh.PRIMITIVE_TRIANGLES)
	
	var step = island_size / float(island_resolution)
	var half_size = island_size / 2.0
	
	# Deterministic noise offset for this island
	var local_noise_offset = Vector2(rng.randf() * 10000, rng.randf() * 10000)
	
	var max_y = -999.0
	var max_pos = Vector3.ZERO
	
	for z in range(island_resolution):
		for x in range(island_resolution):
			var cx = -half_size + x * step
			var cz = -half_size + z * step
			
			var h1 = _get_height(cx, cz, local_noise_offset)
			var h2 = _get_height(cx + step, cz, local_noise_offset)
			var h3 = _get_height(cx, cz + step, local_noise_offset)
			var h4 = _get_height(cx + step, cz + step, local_noise_offset)
			
			if h1 > max_y: max_y = h1; max_pos = Vector3(cx, h1, cz)
			
			var p1 = Vector3(cx, h1, cz)
			var p2 = Vector3(cx + step, h2, cz)
			var p3 = Vector3(cx, h3, cz + step)
			var p4 = Vector3(cx + step, h4, cz + step)
			
			var c1 = _get_color((p1.y + p3.y + p2.y)/3.0)
			st.set_color(c1)
			st.add_vertex(p1); st.add_vertex(p3); st.add_vertex(p2)
			
			var c2 = _get_color((p2.y + p3.y + p4.y)/3.0)
			st.set_color(c2)
			st.add_vertex(p2); st.add_vertex(p3); st.add_vertex(p4)
			
	st.generate_normals()
	mesh.mesh = st.commit()
	mesh.material_override = shared_material
	mesh.create_trimesh_collision()
	
	# Spawn Vista Point at peak if island is large/high enough
	if max_y > 15.0: # Threshold for a "Vista"
		var v_scene = load("res://scenes/VistaPoint.tscn")
		var v = v_scene.instantiate()
		mesh.add_child(v)
		v.position = max_pos + Vector3(0, 1, 0)
		v.vista_id = "Vista_" + str(str(pos).hash())
		
		# Rare Relic spawn at peak
		if rng.randf() < 0.05:
			var r_scene = load("res://scenes/AncientRelic.tscn")
			var r = r_scene.instantiate()
			mesh.add_child(r)
			r.position = max_pos + Vector3(1.5, 0, 1.5) # Offset slightly from Vista point
	
	_spawn_treasures(mesh, local_noise_offset, rng)
	_spawn_foliage(mesh, local_noise_offset, rng)
	_spawn_fireflies(mesh, rng)
	
	if rng.randf() < 0.2:
		_spawn_landmark(mesh, local_noise_offset, rng)
	
	_spawn_collectibles(mesh, local_noise_offset, rng)
	
	return mesh

func _spawn_collectibles(parent_mesh: MeshInstance3D, offset: Vector2, rng: RandomNumberGenerator):
	var m_scene = load("res://scenes/GlowingMushroom.tscn")
	var s_scene = load("res://scenes/StarShell.tscn")
	var c_scene = load("res://scenes/AncientCoin.tscn")
	
	for i in range(rng.randi_range(3, 8)):
		var rx = rng.randf_range(-island_size/2.5, island_size/2.5)
		var rz = rng.randf_range(-island_size/2.5, island_size/2.5)
		var ry = _get_height(rx, rz, offset)
		
		if ry > 0.5:
			var item = null
			if ry < 2.0: # Beach Area
				if rng.randf() < 0.6: item = s_scene.instantiate()
			elif ry > 2.0: # Jungle Area
				if rng.randf() < 0.4: item = m_scene.instantiate()
			
			# Rare Coin check (independent of height)
			if not item and rng.randf() < 0.05:
				item = c_scene.instantiate()
				
			if item:
				parent_mesh.add_child(item)
				item.position = Vector3(rx, ry, rz)
				item.rotate_y(rng.randf() * TAU)

func _spawn_landmark(parent_mesh: MeshInstance3D, offset: Vector2, rng: RandomNumberGenerator):
	var l_scene = load("res://scenes/Landmark.tscn")
	var l = l_scene.instantiate()
	
	# Find a random flat spot
	var attempts = 0
	while attempts < 20:
		attempts += 1
		var rx = rng.randf_range(-island_size/3.0, island_size/3.0)
		var rz = rng.randf_range(-island_size/3.0, island_size/3.0)
		var ry = _get_height(rx, rz, offset)
		if ry > 4.0: # Higher up for landmarks
			parent_mesh.add_child(l)
			l.position = Vector3(rx, ry, rz)
			# Randomize landmark type
			if rng.randf() > 0.5:
				l.landmark_name = "Ancient Statue"
			else:
				l.landmark_name = "Guardian Totem"
			break

func _create_whale_spout(c_pos: Vector2i, rng: RandomNumberGenerator) -> Node3D:
	var spout_scene = load("res://scenes/WhaleSpawnPoint.tscn")
	var spout = spout_scene.instantiate()
	var offset_x = rng.randf_range(0, chunk_size)
	var offset_z = rng.randf_range(0, chunk_size)
	spout.position = Vector3(c_pos.x * chunk_size + offset_x, 0, c_pos.y * chunk_size + offset_z)
	return spout

# Preservation of all existing generation functions (Foliage, Treasures, etc.)
func _spawn_foliage(parent_mesh: MeshInstance3D, offset: Vector2, rng: RandomNumberGenerator):
	var tree_mesh = _create_palm_tree_mesh()
	var bush_mesh = _create_bush_mesh()
	
	var tree_mm = MultiMeshInstance3D.new()
	tree_mm.multimesh = MultiMesh.new()
	tree_mm.multimesh.transform_format = MultiMesh.TRANSFORM_3D
	tree_mm.multimesh.mesh = tree_mesh
	tree_mm.multimesh.instance_count = foliage_density
	parent_mesh.add_child(tree_mm)
	
	var bush_mm = MultiMeshInstance3D.new()
	bush_mm.multimesh = MultiMesh.new()
	bush_mm.multimesh.transform_format = MultiMesh.TRANSFORM_3D
	bush_mm.multimesh.mesh = bush_mesh
	bush_mm.multimesh.instance_count = foliage_density
	parent_mesh.add_child(bush_mm)

	var t_idx = 0; var b_idx = 0
	for i in range(foliage_density * 2):
		var rx = rng.randf_range(-island_size/2.0, island_size/2.0)
		var rz = rng.randf_range(-island_size/2.0, island_size/2.0)
		var ry = _get_height(rx, rz, offset)
		if ry > 2.0:
			var xform = Transform3D(Basis().rotated(Vector3.UP, rng.randf() * TAU).scaled(Vector3.ONE * rng.randf_range(0.8, 1.5)), Vector3(rx, ry, rz))
			if rng.randf() > 0.4 and t_idx < foliage_density:
				tree_mm.multimesh.set_instance_transform(t_idx, xform); t_idx += 1
				var col = StaticBody3D.new(); var sh = CollisionShape3D.new(); sh.shape = CylinderShape3D.new(); sh.shape.radius = 0.3; sh.shape.height = 5.0
				col.add_child(sh); parent_mesh.add_child(col); col.position = Vector3(rx, ry + 2.5, rz)
			elif b_idx < foliage_density:
				bush_mm.multimesh.set_instance_transform(b_idx, xform); b_idx += 1

func _spawn_treasures(parent_mesh: MeshInstance3D, offset: Vector2, rng: RandomNumberGenerator):
	var t_scene = load("res://scenes/TreasureBox.tscn")
	for i in range(rng.randi_range(1, 3)):
		var rx = rng.randf_range(-island_size/4.0, island_size/4.0); var rz = rng.randf_range(-island_size/4.0, island_size/4.0); var ry = _get_height(rx, rz, offset)
		if ry > 2.0:
			var tb = t_scene.instantiate(); parent_mesh.add_child(tb); tb.position = Vector3(rx, ry, rz)

func _get_height(x: float, z: float, offset: Vector2) -> float:
	var raw = noise.get_noise_2d(x + offset.x, z + offset.y)
	var falloff = smoothstep(0.0, 1.0, clamp(1.0 - (Vector2(x, z).length() / (island_size/2.0)), 0.0, 1.0))
	return ( (raw + 1.0) / 2.0 ) * falloff * height_multiplier - 5.0

func _get_color(y: float) -> Color:
	return Color("e0c98b") if y < 1.0 else Color("4caf50")

func _create_palm_tree_mesh() -> Mesh:
	var st = SurfaceTool.new(); st.begin(Mesh.PRIMITIVE_TRIANGLES); st.set_color(Color("5d4037"))
	var rad = 0.3; var h = 5.0
	for i in range(6):
		var a1 = (i/6.0)*TAU; var a2 = ((i+1)/6.0)*TAU; var x1 = cos(a1)*rad; var z1 = sin(a1)*rad; var x2 = cos(a2)*rad; var z2 = sin(a2)*rad
		st.add_vertex(Vector3(x1, 0, z1)); st.add_vertex(Vector3(x1, h, z1)); st.add_vertex(Vector3(x2, h, z2))
		st.add_vertex(Vector3(x1, 0, z1)); st.add_vertex(Vector3(x2, h, z2)); st.add_vertex(Vector3(x2, 0, z2))
	st.set_color(Color("2e7d32"))
	for i in range(5):
		var ang = (i/5.0)*TAU; var x = cos(ang)*3.0; var z = sin(ang)*3.0
		st.add_vertex(Vector3(0, h, 0)); st.add_vertex(Vector3(x, h-1.0, z)); st.add_vertex(Vector3(cos(ang+0.5)*3.0, h-1.5, sin(ang+0.5)*3.0))
	st.generate_normals(); return st.commit()

func _create_bush_mesh() -> Mesh:
	var st = SurfaceTool.new(); st.begin(Mesh.PRIMITIVE_TRIANGLES); st.set_color(Color("1b5e20"))
	for i in range(8):
		var ang = (i/8.0)*TAU
		st.add_vertex(Vector3(0, 1.5, 0)); st.add_vertex(Vector3(cos(ang)*1.5, 0.5, sin(ang)*1.5)); st.add_vertex(Vector3(cos(ang+0.8)*1.5, 0.5, sin(ang+0.8)*1.5))
	st.generate_normals(); return st.commit()

func _spawn_fireflies(parent_mesh: MeshInstance3D, rng: RandomNumberGenerator):
	var particles = GPUParticles3D.new()
	var mat = ParticleProcessMaterial.new()
	mat.emission_shape = ParticleProcessMaterial.EMISSION_SHAPE_BOX
	mat.emission_box_extents = Vector3(island_size / 3.0, 10.0, island_size / 3.0)
	mat.gravity = Vector3(0, 0.2, 0)
	mat.direction = Vector3(1, 1, 1)
	mat.spread = 180.0
	mat.initial_velocity_min = 0.5
	mat.initial_velocity_max = 1.2
	
	particles.process_material = mat
	particles.amount = 100
	particles.lifetime = 10.0
	particles.preprocess = 5.0
	particles.visibility_aabb = AABB(Vector3(-island_size/2, -50, -island_size/2), Vector3(island_size, 100, island_size))
	
	var pass_mesh = QuadMesh.new()
	pass_mesh.size = Vector2(0.2, 0.2)
	var pass_mat = StandardMaterial3D.new()
	pass_mat.shading_mode = StandardMaterial3D.SHADING_MODE_UNSHADED
	pass_mat.billboard_mode = StandardMaterial3D.BILLBOARD_PARTICLES
	pass_mat.albedo_color = Color(1.5, 1.5, 0.5, 1.0) # Glowing yellow
	pass_mesh.material = pass_mat
	
	particles.draw_pass_1 = pass_mesh
	particles.add_to_group("night_lights")
	particles.emitting = false # Off initially
	parent_mesh.add_child(particles)
	particles.position = Vector3(0, 5, 0)
