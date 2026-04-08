extends StaticBody3D
class_name Interactable

signal interacted

func interact():
	interacted.emit()
	print("Interacted with: ", name)
