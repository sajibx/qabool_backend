extends Node

const SAVE_PATH = "user://save_data.json"
var auto_save_timer: float = 300.0 # 5 minutes

func _ready():
    # Allow one frame for other singletons to initialize
    await get_tree().process_frame
    load_game()

func _process(delta):
    auto_save_timer -= delta
    if auto_save_timer <= 0:
        save_game()
        auto_save_timer = 300.0

func save_game():
    var save_dict = {
        "game_manager": {
            "treasure_count": GameManager.treasure_count,
            "fish_count": GameManager.fish_count,
            "whale_bone": GameManager.whale_bone,
            "discovery_points": GameManager.discovery_points,
            "discovered_landmarks": GameManager.discovered_landmarks,
            "unlocked_cosmetics": GameManager.unlocked_cosmetics,
            "mushroom_count": GameManager.mushroom_count,
            "shell_count": GameManager.shell_count,
            "coin_count": GameManager.coin_count,
            "sketched_vistas": GameManager.sketched_vistas,
            "ship_blueprint_count": GameManager.ship_blueprint_count,
            "speed_level": GameManager.speed_level,
            "cannon_level": GameManager.cannon_level,
            "hull_armor_level": GameManager.hull_armor_level,
            "harpoon_damage_level": GameManager.harpoon_damage_level
        },
        "world": {
            "seed": 0 # Default if no generator found
        },
        "ship": {
            "x": 0.0, "y": 0.0, "z": 0.0
        }
    }
    
    # World Seed
    var generator = get_tree().current_scene.get_node_or_null("IslandGenerator")
    if generator and generator.noise:
        save_dict["world"]["seed"] = generator.noise.seed
    
    # Ship Position
    var ship = get_tree().get_first_node_in_group("player_ship")
    if ship:
        save_dict["ship"]["x"] = ship.global_position.x
        save_dict["ship"]["y"] = ship.global_position.y
        save_dict["ship"]["z"] = ship.global_position.z
        
    var file = FileAccess.open(SAVE_PATH, FileAccess.WRITE)
    if file:
        file.store_string(JSON.stringify(save_dict))
        print("Game Saved to: ", SAVE_PATH)

func load_game():
    if not FileAccess.file_exists(SAVE_PATH):
        print("No save file found. Starting fresh voyage.")
        return
        
    var file = FileAccess.open(SAVE_PATH, FileAccess.READ)
    var content = file.get_as_text()
    var save_dict = JSON.parse_string(content)
    
    if not save_dict:
        print("Error parsing save file!")
        return
        
    # Restore GameManager
    var gm_data = save_dict.get("game_manager", {})
    for key in gm_data.keys():
        GameManager.set(key, gm_data[key])
    
    # Restore World Seed
    var world_data = save_dict.get("world", {})
    var generator = get_tree().current_scene.get_node_or_null("IslandGenerator")
    if generator and world_data.has("seed"):
        generator.set_noise_seed(world_data["seed"])
    
    # Restore Ship Position
    # We use a slight delay or call it later if ship isn't ready
    var ship_data = save_dict.get("ship", {})
    var ship = get_tree().get_first_node_in_group("player_ship")
    if ship:
        ship.global_position = Vector3(ship_data.get("x", 0), ship_data.get("y", 0), ship_data.get("z", 0))
        
    print("Game Loaded Successfully.")
