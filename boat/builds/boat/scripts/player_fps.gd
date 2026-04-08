extends CharacterBody3D

const SPEED = 10.0
const JUMP_VELOCITY = 6.0
var gravity = 15.0

# Head-bob
const BOB_FREQ = 2.4
const BOB_AMP = 0.08
var bob_time = 0.0

@onready var head = $Head
@onready var camera = $Head/Camera3D
@onready var interact_ray = $Head/Camera3D/RayCast3D

func _ready():
    Input.set_mouse_mode(Input.MOUSE_MODE_CAPTURED)

func _unhandled_input(event):
    if event is InputEventMouseMotion and Input.get_mouse_mode() == Input.MOUSE_MODE_CAPTURED:
        rotate_y(-event.relative.x * 0.005)
        head.rotate_x(-event.relative.y * 0.005)
        head.rotation.x = clamp(head.rotation.x, -PI/2, PI/2)
    elif event.is_action_pressed("ui_cancel"):
        if Input.get_mouse_mode() == Input.MOUSE_MODE_CAPTURED:
            Input.set_mouse_mode(Input.MOUSE_MODE_VISIBLE)
        else:
            Input.set_mouse_mode(Input.MOUSE_MODE_CAPTURED)
            
    if event.is_action_pressed("interact") or (event is InputEventMouseButton and event.pressed):
        if interact_ray.is_colliding():
            var collider = interact_ray.get_collider()
            if collider.has_method("interact"):
                collider.interact()

func _physics_process(delta):
    _process_interaction()
    if not is_on_floor():
        velocity.y -= gravity * delta

    if Input.is_physical_key_pressed(KEY_SPACE) and is_on_floor():
        velocity.y = JUMP_VELOCITY

    # Check HUD
    var hud = get_tree().current_scene.get_node_or_null("MobileHUD")
    var joy_l = Vector2.ZERO
    var joy_r = Vector2.ZERO
    if hud:
        joy_l = hud.get_node("LeftJoystick").get_output()
        joy_r = hud.get_node("RightJoystick").get_output()
        
    # Rotate using right joystick
    if joy_r.length() > 0.1:
        rotate_y(-joy_r.x * 0.05)
        head.rotate_x(-joy_r.y * 0.05)
        head.rotation.x = clamp(head.rotation.x, -PI/2, PI/2)

    var input_dir = Vector2.ZERO
    if Input.is_physical_key_pressed(KEY_W): input_dir.y -= 1
    if Input.is_physical_key_pressed(KEY_S): input_dir.y += 1
    if Input.is_physical_key_pressed(KEY_A): input_dir.x -= 1
    if Input.is_physical_key_pressed(KEY_D): input_dir.x += 1
    
    # Add joystick input
    input_dir += joy_l
    
    input_dir = input_dir.normalized()
    
    var direction = (transform.basis * Vector3(input_dir.x, 0, input_dir.y)).normalized()
    if direction:
        velocity.x = direction.x * SPEED
        velocity.z = direction.z * SPEED
    else:
        velocity.x = move_toward(velocity.x, 0, SPEED)
        velocity.z = move_toward(velocity.z, 0, SPEED)

    # Bobbing
    bob_time += delta * velocity.length() * float(is_on_floor())
    camera.transform.origin = _headbob(bob_time)

    move_and_slide()

func _headbob(time) -> Vector3:
    var pos = Vector3.ZERO
    pos.y = sin(time * BOB_FREQ) * BOB_AMP
    pos.x = cos(time * BOB_FREQ / 2) * BOB_AMP
    return pos

func _process_interaction():
    var hud = get_tree().current_scene.get_node_or_null("MobileHUD")
    if not hud: return
    
    var is_focused = false
    if interact_ray.is_colliding():
        var collider = interact_ray.get_collider()
        if collider.has_method("interact"):
            is_focused = true
            
    hud.set_crosshair_focused(is_focused)
