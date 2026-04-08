shader_type spatial;
render_mode blend_mix, depth_draw_opaque, cull_back, diffuse_burley, specular_schlick_ggx;

precision mediump float;

uniform vec3 water_color : source_color = vec3(0.0, 0.6, 1.0);
uniform float wave_speed = 1.5;
uniform float wave_height = 0.8;
uniform float wave_frequency = 1.5;
uniform float bioluminescence : hint_range(0.0, 10.0) = 0.0;
uniform vec3 bioluminescence_color : source_color = vec3(0.0, 1.0, 1.0);

void vertex() {
    float wave1 = sin(VERTEX.x * wave_frequency + TIME * wave_speed);
    float wave2 = cos(VERTEX.z * wave_frequency + TIME * wave_speed * 1.2);
    float wave3 = sin((VERTEX.x + VERTEX.z) * wave_frequency * 0.7 - TIME * wave_speed * 0.8);
    
    // Mix overlapping sine waves to create faceted displacement
    VERTEX.y = (wave1 + wave2 + wave3) * wave_height / 3.0;
}

void fragment() {
    ALBEDO = water_color;
    ROUGHNESS = 0.2;
    METALLIC = 0.1;

    // Glowing crests (Bioluminescence)
    float height_factor = clamp(VERTEX.y / wave_height, 0.0, 1.0);
    EMISSION = bioluminescence_color * bioluminescence * pow(height_factor, 2.0);
    
    // Flat shading calculation using screen space derivatives of the view-space vertex position
    vec3 xTangent = dFdx(VERTEX);
    vec3 yTangent = dFdy(VERTEX);
    
    NORMAL = normalize(cross(dFdx(VERTEX), dFdy(VERTEX)));
}
