extends Node

signal treasure_collected(count)
signal fish_caught_updated(count)
signal whale_bone_updated(count)
signal discovery_updated(points)
signal landmark_discovered(id)
signal cosmetic_unlocked(name)
signal inventory_updated(type, count)
signal vista_sketched(id)
signal blueprint_collected(count)
signal teleport_started()

var treasure_count: int = 0
var fish_count: int = 0
var whale_bone: int = 0
var discovery_points: int = 0
var discovered_landmarks: Array[String] = []
var unlocked_cosmetics: Array[String] = []
var mushroom_count: int = 0
var shell_count: int = 0
var coin_count: int = 0
var sketched_vistas: Array[String] = []
var ship_blueprint_count: int = 0
var speed_level: int = 0
var cannon_level: int = 0
var hull_armor_level: int = 0
var harpoon_damage_level: int = 0

func collect_treasure():
    treasure_count += 1
    treasure_collected.emit(treasure_count)

func spend_treasure(amount: int) -> bool:
    if treasure_count >= amount:
        treasure_count -= amount
        treasure_collected.emit(treasure_count)
        return true
    return false

func sell_all_fish():
    if fish_count > 0:
        var earnings = fish_count * 2
        treasure_count += earnings
        fish_count = 0
        treasure_collected.emit(treasure_count)
        fish_caught_updated.emit(fish_count)
        print("Sold all fish for ", earnings, " Treasure!")

func add_whale_bone(amount: int):
    whale_bone += amount
    whale_bone_updated.emit(whale_bone)
    print("Whale Bone added! Total: ", whale_bone)

func catch_fish():
    fish_count += 1
    fish_caught_updated.emit(fish_count)
    print("Fish caught! Total: ", fish_count)

func spend_whale_bone(amount: int) -> bool:
    if whale_bone >= amount:
        whale_bone -= amount
        whale_bone_updated.emit(whale_bone)
        return true
    return false

func add_discovery_points(amount: int, landmark_id: String):
    if not landmark_id in discovered_landmarks:
        discovery_points += amount
        discovered_landmarks.append(landmark_id)
        discovery_updated.emit(discovery_points)
        landmark_discovered.emit(landmark_id)
        print("New Landmark Discovered: ", landmark_id, " (+", amount, " Points)")

func unlock_cosmetic(name: String, cost: int) -> bool:
    if discovery_points >= cost and not name in unlocked_cosmetics:
        discovery_points -= cost
        unlocked_cosmetics.append(name)
        discovery_updated.emit(discovery_points)
        cosmetic_unlocked.emit(name)
        print("Cosmetic Unlocked: ", name)
        return true
    return false

func collect_item(type: String, amount: int = 1):
    match type:
        "mushroom": mushroom_count += amount
        "shell": shell_count += amount
        "coin": coin_count += amount
    inventory_updated.emit(type, amount)
    print("Collected: ", type, " (x", amount, ")")

func add_vista_sketch(id: String):
    if not id in sketched_vistas:
        sketched_vistas.append(id)
        discovery_points += 50
        discovery_updated.emit(discovery_points)
        vista_sketched.emit(id)
        print("Vista Sketched: ", id, " (+50 Points)")

func add_ship_blueprint():
    ship_blueprint_count += 1
    blueprint_collected.emit(ship_blueprint_count)
    print("Ship Blueprint Collected! Total: ", ship_blueprint_count)

func fast_travel_home():
    var ship = get_tree().get_first_node_in_group("player_ship")
    if not ship: return
    
    teleport_started.emit()
    ship.global_position = Vector3(0, 5, 50) # HomeBase Docking Area
    ship.rotation = Vector3.ZERO
    ship.velocity = Vector3.ZERO
    print("Fast Traveled Home.")

func get_hull_armor_multiplier() -> float:
    return 1.0 + (hull_armor_level * 0.5) # 50% health boost per level

func get_harpoon_damage_multiplier() -> float:
    return 1.0 + (harpoon_damage_level * 1.0) # Double damage per level
