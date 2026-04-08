shader_type spatial;
render_mode blend_mix, depth_draw_opaque, cull_back, diffuse_burley, specular_schlick_ggx;

precision medium_p float;

uniform vec3 water_color : source_color = vec3(0.00, 0.40, 0.80);
uniform vec3 shallow_color : source_color = vec3(0.00, 0.85, 1.00);
uniform float wave_speed = 1.0;
uniform float wave_height = 1.2;
uniform float wave_frequency = 0.5;
uniform float foam_level : hint_range(0.0, 1.0) = 0.3;

varying float v_height;
varying vec3 v_world_pos;

// Mobile-safe wave function
float get_wave(vec2 pos, float t) {
    float w1 = sin(pos.x * wave_frequency + t * wave_speed);
    float w2 = cos(pos.y * wave_frequency * 0.8 + t * wave_speed * 1.1);
    float w3 = sin((pos.x + pos.y) * wave_frequency * 0.6 - t * wave_speed * 0.9);
    return (w1 + w2 + w3) * wave_height / 3.0;
}

void vertex() {
    v_world_pos = (MODEL_MATRIX * vec4(VERTEX, 1.0)).xyz;
    v_height = get_wave(v_world_pos.xz, TIME);
    VERTEX.y = v_height;
}

void fragment() {
    float t = TIME * wave_speed;
    vec2 pos = v_world_pos.xz;
    
    // Calculate Normal via partial samples (Mobile-Safe Replacement for dFdx/dFdy)
    float e = 0.1;
    float h0 = get_wave(pos, TIME);
    float hx = get_wave(pos + vec2(e, 0.0), TIME);
    float hz = get_wave(pos + vec2(0.0, e), TIME);
    
    vec3 v1 = vec3(e, hx - h0, 0.0);
    vec3 v2 = vec3(0.0, hz - h0, e);
    vec3 normal = normalize(cross(v2, v1));
    
    // Depth Color Transition
    float depth_factor = clamp((v_height + wave_height) / (wave_height * 2.0), 0.0, 1.0);
    vec3 final_color = mix(water_color, shallow_color, depth_factor);
    
    // Foam at crests
    float foam_factor = smoothstep(wave_height * (1.0 - foam_level), wave_height, v_height);
    final_color = mix(final_color, vec3(1.0, 1.0, 1.0), foam_factor * 0.4);
    
    ALBEDO = final_color;
    NORMAL = normal;
    ROUGHNESS = 0.05;
    METALLIC = 0.0;
    SPECULAR = 0.5;
    
    // Soft Glow at crests for bioluminescence feel
    EMISSION = vec3(0.0, 0.5, 0.5) * foam_factor * 0.2;
}
