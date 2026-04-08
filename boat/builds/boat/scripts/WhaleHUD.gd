extends CanvasLayer

@onready var whale_bar = $VBoxContainer/WhaleHealthBar
@onready var boat_bar = $VBoxContainer/BoatHealthBar
@onready var message_label = $MessageLabel

func _ready():
	message_label.visible = false
	whale_bar.value = 100
	boat_bar.value = 100

func update_whale_health(val):
	whale_bar.value = val

func update_boat_health(val):
	boat_bar.value = val

func show_message(text):
	message_label.text = text
	message_label.visible = true
