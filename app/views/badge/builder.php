<?php
$title = 'Visual Badge Builder';
require APP_PATH . '/views/partials/header.php';
?>

<section class="py-4">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">
                    <span class="material-symbols-rounded align-middle me-2" style="color:var(--md-primary)">palette</span>
                    Visual Badge Builder
                </h3>
                <p class="text-muted mb-0">Design professional badges for your credentials</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" id="downloadPng" class="btn btn-outline-secondary rounded-pill">
                    <span class="material-symbols-rounded btn-icon">image</span>
                    Export PNG
                </button>
                <button type="button" id="downloadBadge" class="btn btn-outline-primary rounded-pill">
                    <span class="material-symbols-rounded btn-icon">download</span>
                    Export SVG
                </button>
                <button type="button" id="saveBadge" class="btn btn-primary rounded-pill">
                    <span class="material-symbols-rounded btn-icon">save</span>
                    Save Badge
                </button>
            </div>
        </div>

        <div class="row g-4">
            <!-- Controls Panel -->
            <div class="col-lg-4 col-xl-3">
                <div class="accordion" id="badgeControls">

                    <!-- Shape & Size -->
                    <div class="accordion-item border-0 rounded-3 mb-2 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button rounded-3 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#panelShape">
                                <span class="material-symbols-rounded me-2" style="font-size:20px">category</span>
                                Shape & Size
                            </button>
                        </h2>
                        <div id="panelShape" class="accordion-collapse collapse show" data-bs-parent="#badgeControls">
                            <div class="accordion-body">
                                <label class="form-label small fw-semibold">Shape</label>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php foreach (['circle' => 'Circle', 'rounded' => 'Rounded', 'hex' => 'Hexagon', 'shield' => 'Shield', 'star' => 'Star', 'diamond' => 'Diamond', 'octagon' => 'Octagon', 'ribbon' => 'Ribbon'] as $val => $lbl): ?>
                                    <div>
                                        <input type="radio" class="btn-check" name="shape" id="shape_<?= $val ?>" value="<?= $val ?>" <?= $val === 'circle' ? 'checked' : '' ?>>
                                        <label class="btn btn-sm btn-outline-primary rounded-pill px-3" for="shape_<?= $val ?>"><?= $lbl ?></label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <label class="form-label small fw-semibold">Size: <span id="sizeLabel">250</span>px</label>
                                <input type="range" class="form-range" id="badgeSize" min="120" max="500" value="250">

                                <label class="form-label small fw-semibold mt-2">Rotation: <span id="rotLabel">0</span>&deg;</label>
                                <input type="range" class="form-range" id="badgeRotation" min="0" max="360" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- Colors & Gradient -->
                    <div class="accordion-item border-0 rounded-3 mb-2 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#panelColors">
                                <span class="material-symbols-rounded me-2" style="font-size:20px">format_color_fill</span>
                                Colors & Gradient
                            </button>
                        </h2>
                        <div id="panelColors" class="accordion-collapse collapse" data-bs-parent="#badgeControls">
                            <div class="accordion-body">
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Primary</label>
                                        <input type="color" id="primaryColor" class="form-control form-control-color w-100" value="#2962FF">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Secondary</label>
                                        <input type="color" id="secondaryColor" class="form-control form-control-color w-100" value="#FFD600">
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Background</label>
                                        <input type="color" id="bgColor" class="form-control form-control-color w-100" value="#FFFFFF">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Text Color</label>
                                        <input type="color" id="textColor" class="form-control form-control-color w-100" value="#1A1A1A">
                                    </div>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="useGradient">
                                    <label class="form-check-label small" for="useGradient">Use gradient fill</label>
                                </div>
                                <div id="gradientSettings" style="display:none">
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small">Gradient Start</label>
                                            <input type="color" id="gradStart" class="form-control form-control-color w-100" value="#667eea">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Gradient End</label>
                                            <input type="color" id="gradEnd" class="form-control form-control-color w-100" value="#764ba2">
                                        </div>
                                    </div>
                                    <label class="form-label small">Direction</label>
                                    <select id="gradDirection" class="form-select form-select-sm">
                                        <option value="tb">Top → Bottom</option>
                                        <option value="lr">Left → Right</option>
                                        <option value="diag">Diagonal</option>
                                        <option value="radial">Radial</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Text -->
                    <div class="accordion-item border-0 rounded-3 mb-2 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#panelText">
                                <span class="material-symbols-rounded me-2" style="font-size:20px">text_fields</span>
                                Text & Labels
                            </button>
                        </h2>
                        <div id="panelText" class="accordion-collapse collapse" data-bs-parent="#badgeControls">
                            <div class="accordion-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Title</label>
                                    <input type="text" id="badgeTitle" class="form-control form-control-sm" value="Achievement" maxlength="30">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Subtitle</label>
                                    <input type="text" id="badgeSubtitle" class="form-control form-control-sm" value="Certified" maxlength="40">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Year / Number</label>
                                    <input type="text" id="badgeYear" class="form-control form-control-sm" value="2026" maxlength="10">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Font</label>
                                    <select id="badgeFont" class="form-select form-select-sm">
                                        <option value="sans-serif" selected>Sans Serif</option>
                                        <option value="serif">Serif</option>
                                        <option value="monospace">Monospace</option>
                                        <option value="'Georgia', serif">Georgia</option>
                                        <option value="'Trebuchet MS', sans-serif">Trebuchet</option>
                                    </select>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="textBold" checked>
                                    <label class="form-check-label small" for="textBold">Bold title</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="textShadow">
                                    <label class="form-check-label small" for="textShadow">Text shadow</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Icon -->
                    <div class="accordion-item border-0 rounded-3 mb-2 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#panelIcon">
                                <span class="material-symbols-rounded me-2" style="font-size:20px">mood</span>
                                Icon & Symbol
                            </button>
                        </h2>
                        <div id="panelIcon" class="accordion-collapse collapse" data-bs-parent="#badgeControls">
                            <div class="accordion-body">
                                <!-- Icon source toggle -->
                                <div class="btn-group btn-group-sm w-100 mb-3" role="group">
                                    <input type="radio" class="btn-check" name="iconSource" id="iconSourceBuiltin" value="builtin" checked>
                                    <label class="btn btn-outline-primary" for="iconSourceBuiltin">
                                        <span class="material-symbols-rounded" style="font-size:16px;vertical-align:-3px">emoji_symbols</span>
                                        Built-in
                                    </label>
                                    <input type="radio" class="btn-check" name="iconSource" id="iconSourceUpload" value="upload">
                                    <label class="btn btn-outline-primary" for="iconSourceUpload">
                                        <span class="material-symbols-rounded" style="font-size:16px;vertical-align:-3px">upload_file</span>
                                        Upload Image
                                    </label>
                                </div>

                                <!-- Built-in icons panel -->
                                <div id="builtinIconPanel">
                                    <label class="form-label small fw-semibold">Select Icon</label>
                                    <div class="d-flex flex-wrap gap-1 mb-3">
                                        <?php
                                        $icons = [
                                            'star', 'verified', 'workspace_premium', 'military_tech', 'school',
                                            'emoji_events', 'thumb_up', 'rocket_launch', 'bolt', 'diamond',
                                            'favorite', 'shield', 'psychology', 'code', 'science',
                                            'auto_awesome', 'local_fire_department', 'music_note', 'brush',
                                            'fitness_center', 'public', 'lightbulb', 'engineering', 'biotech',
                                            'grade', 'celebration', 'handshake', 'groups', 'trending_up', 'token'
                                        ];
                                        foreach ($icons as $icon):
                                        ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary icon-picker rounded-2 p-1" data-icon="<?= $icon ?>" title="<?= $icon ?>" style="width:38px;height:38px">
                                            <span class="material-symbols-rounded" style="font-size:20px"><?= $icon ?></span>
                                        </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Upload image panel -->
                                <div id="uploadIconPanel" style="display:none">
                                    <label class="form-label small fw-semibold">Upload Icon Image</label>
                                    <div class="d-flex gap-2 align-items-start mb-2">
                                        <div id="customIconPreview" style="width:64px;height:64px;border:2px dashed var(--md-outline);border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">
                                            <span class="material-symbols-rounded text-muted" style="font-size:28px">image</span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="file" id="customIconFile" class="form-control form-control-sm" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/webp">
                                            <div class="form-text">PNG, JPG, SVG, GIF, WebP. Max 2MB.</div>
                                            <button type="button" id="clearCustomIcon" class="btn btn-sm btn-outline-danger rounded-pill mt-1" style="display:none">
                                                <span class="material-symbols-rounded" style="font-size:14px;vertical-align:-2px">close</span>
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-text text-info mb-2">
                                        <span class="material-symbols-rounded" style="font-size:14px;vertical-align:-2px">info</span>
                                        Image is embedded directly in the SVG — works offline and in exports.
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Icon Size: <span id="iconSizeLabel">100</span>%</label>
                                    <input type="range" class="form-range" id="iconSize" min="50" max="200" value="100">
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="showIcon" checked>
                                    <label class="form-check-label small" for="showIcon">Show icon</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Border & Effects -->
                    <div class="accordion-item border-0 rounded-3 mb-2 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#panelBorder">
                                <span class="material-symbols-rounded me-2" style="font-size:20px">border_style</span>
                                Border & Effects
                            </button>
                        </h2>
                        <div id="panelBorder" class="accordion-collapse collapse" data-bs-parent="#badgeControls">
                            <div class="accordion-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Border Style</label>
                                    <select id="borderStyle" class="form-select form-select-sm">
                                        <option value="solid">Solid</option>
                                        <option value="double">Double</option>
                                        <option value="dashed">Dashed</option>
                                        <option value="dotted">Dotted</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Border Width: <span id="borderWidthLabel">3</span>px</label>
                                    <input type="range" class="form-range" id="borderWidth" min="1" max="10" value="3">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Border Color</label>
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" id="borderMatchPrimary" checked>
                                        <label class="form-check-label small" for="borderMatchPrimary">Match primary color</label>
                                    </div>
                                    <input type="color" id="borderColor" class="form-control form-control-color w-100" value="#2962FF" disabled>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="showDropShadow">
                                    <label class="form-check-label small" for="showDropShadow">Drop shadow</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="showInnerGlow">
                                    <label class="form-check-label small" for="showInnerGlow">Inner glow</label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Opacity</label>
                                    <input type="range" class="form-range" id="bgOpacity" min="0" max="100" value="100">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Decorations -->
                    <div class="accordion-item border-0 rounded-3 mb-2 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#panelDecor">
                                <span class="material-symbols-rounded me-2" style="font-size:20px">auto_fix_high</span>
                                Decorations
                            </button>
                        </h2>
                        <div id="panelDecor" class="accordion-collapse collapse" data-bs-parent="#badgeControls">
                            <div class="accordion-body">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="showStars" checked>
                                    <label class="form-check-label small" for="showStars">Accent stars</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="showLaurel">
                                    <label class="form-check-label small" for="showLaurel">Laurel wreath</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="showRibbon">
                                    <label class="form-check-label small" for="showRibbon">Bottom ribbon</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="showPattern">
                                    <label class="form-check-label small" for="showPattern">Background pattern</label>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Pattern</label>
                                    <select id="patternType" class="form-select form-select-sm">
                                        <option value="dots">Dots</option>
                                        <option value="lines">Lines</option>
                                        <option value="crosshatch">Crosshatch</option>
                                        <option value="waves">Waves</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Badge Name (for saving) -->
                <div class="material-card mt-3">
                    <label class="form-label small fw-semibold">Badge Name</label>
                    <input type="text" id="badgeName" class="form-control form-control-sm" value="My Custom Badge">
                </div>
            </div>

            <!-- Preview -->
            <div class="col-lg-5 col-xl-6">
                <div class="card shadow-sm border-0" style="border-radius:16px">
                    <div class="card-header bg-transparent border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-semibold mb-0">
                            <span class="material-symbols-rounded align-middle me-1" style="font-size:20px">visibility</span>
                            Live Preview
                        </h6>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary bg-btn active" data-bg="checker">
                                <span class="material-symbols-rounded" style="font-size:16px">grid_on</span>
                            </button>
                            <button class="btn btn-outline-secondary bg-btn" data-bg="light">
                                <span class="material-symbols-rounded" style="font-size:16px">light_mode</span>
                            </button>
                            <button class="btn btn-outline-secondary bg-btn" data-bg="dark">
                                <span class="material-symbols-rounded" style="font-size:16px">dark_mode</span>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4 d-flex justify-content-center align-items-center" id="previewContainer"
                         style="min-height:460px;background:repeating-conic-gradient(#f0f0f0 0% 25%, white 0% 50%) 50% / 20px 20px;border-radius:0 0 16px 16px">
                        <div id="badgePreview"></div>
                    </div>
                </div>
            </div>

            <!-- Presets & Saved -->
            <div class="col-lg-3">
                <div class="card shadow-sm border-0 mb-3" style="border-radius:16px">
                    <div class="card-header bg-transparent border-0 pt-3 px-4">
                        <h6 class="fw-semibold mb-0">
                            <span class="material-symbols-rounded align-middle me-1" style="font-size:20px">auto_awesome</span>
                            Quick Presets
                        </h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="d-flex flex-wrap gap-2">
                            <?php
                            $presetList = [
                                'gold' => ['emoji_events', 'Gold Award'],
                                'silver' => ['workspace_premium', 'Silver Badge'],
                                'bronze' => ['military_tech', 'Bronze Medal'],
                                'tech' => ['bolt', 'Tech Pro'],
                                'academic' => ['school', 'Academic'],
                                'modern' => ['diamond', 'Modern'],
                                'minimal' => ['verified', 'Minimal'],
                                'neon' => ['auto_awesome', 'Neon Glow'],
                                'corporate' => ['handshake', 'Corporate'],
                                'creative' => ['brush', 'Creative'],
                                'fire' => ['local_fire_department', 'Fire Badge'],
                                'science' => ['science', 'Science'],
                            ];
                            foreach ($presetList as $key => $meta):
                            ?>
                            <button class="btn btn-sm btn-outline-primary rounded-pill preset-btn d-flex align-items-center gap-1" data-preset="<?= $key ?>">
                                <span class="material-symbols-rounded" style="font-size:16px"><?= $meta[0] ?></span>
                                <span class="small"><?= $meta[1] ?></span>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Color Palettes -->
                <div class="card shadow-sm border-0 mb-3" style="border-radius:16px">
                    <div class="card-header bg-transparent border-0 pt-3 px-4">
                        <h6 class="fw-semibold mb-0">
                            <span class="material-symbols-rounded align-middle me-1" style="font-size:20px">palette</span>
                            Color Palettes
                        </h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="d-flex flex-column gap-2">
                            <?php
                            $palettes = [
                                ['Ocean',   '#0f4c75', '#3282b8', '#bbe1fa', '#1b262c'],
                                ['Sunset',  '#e63946', '#f1a94e', '#ffecd2', '#264653'],
                                ['Forest',  '#2d6a4f', '#52b788', '#d8f3dc', '#1b4332'],
                                ['Lavender','#7b2cbf', '#c77dff', '#e0aaff', '#3c096c'],
                                ['Midnight','#073b4c', '#118ab2', '#06d6a0', '#ffd166'],
                                ['Rose',    '#ff006e', '#fb5607', '#ffbe0b', '#8338ec'],
                            ];
                            foreach ($palettes as $p):
                            ?>
                            <button class="btn btn-sm btn-outline-secondary rounded-pill d-flex align-items-center gap-2 palette-btn"
                                    data-primary="<?= $p[1] ?>" data-secondary="<?= $p[2] ?>" data-bg="<?= $p[3] ?>" data-text="<?= $p[4] ?>">
                                <span class="d-flex gap-1">
                                    <span style="width:16px;height:16px;border-radius:50%;background:<?= $p[1] ?>;display:inline-block;border:1px solid rgba(0,0,0,.1)"></span>
                                    <span style="width:16px;height:16px;border-radius:50%;background:<?= $p[2] ?>;display:inline-block;border:1px solid rgba(0,0,0,.1)"></span>
                                    <span style="width:16px;height:16px;border-radius:50%;background:<?= $p[3] ?>;display:inline-block;border:1px solid rgba(0,0,0,.1)"></span>
                                </span>
                                <span class="small"><?= $p[0] ?></span>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="card shadow-sm border-0" style="border-radius:16px;background:var(--md-surface-container-low)">
                    <div class="card-body p-3">
                        <h6 class="fw-semibold small mb-2">
                            <span class="material-symbols-rounded align-middle me-1" style="font-size:18px">tips_and_updates</span>
                            Tips
                        </h6>
                        <ul class="small text-muted mb-0" style="padding-left:1.2rem">
                            <li>Use presets as starting points</li>
                            <li>Enable gradients for modern look</li>
                            <li>Add laurel wreath for academic badges</li>
                            <li>SVG export is lossless at any size</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const preview = document.getElementById('badgePreview');
    let selectedIcon = 'verified';
    let customIconDataUrl = null; // holds uploaded image as data URL
    let iconSource = 'builtin'; // 'builtin' or 'upload'

    // --- Presets ---
    const presets = {
        gold:      { primary:'#FFD700', secondary:'#B8860B', bg:'#FFF8E1', text:'#5D4037', shape:'circle', icon:'emoji_events', title:'Excellence', subtitle:'Gold Award', year:'2026', gradient:false, laurel:true, ribbon:false, stars:true, pattern:false },
        silver:    { primary:'#78909C', secondary:'#455A64', bg:'#ECEFF1', text:'#263238', shape:'circle', icon:'workspace_premium', title:'Achievement', subtitle:'Silver Medal', year:'2026', gradient:false, laurel:false, ribbon:false, stars:true, pattern:false },
        bronze:    { primary:'#A1887F', secondary:'#6D4C41', bg:'#EFEBE9', text:'#3E2723', shape:'shield', icon:'military_tech', title:'Merit', subtitle:'Bronze Honor', year:'2026', gradient:false, laurel:false, ribbon:true, stars:false, pattern:false },
        tech:      { primary:'#00BCD4', secondary:'#006064', bg:'#E0F7FA', text:'#004D40', shape:'hex', icon:'bolt', title:'Certified', subtitle:'Tech Pro', year:'2026', gradient:true, laurel:false, ribbon:false, stars:false, pattern:true },
        academic:  { primary:'#1A237E', secondary:'#3F51B5', bg:'#E8EAF6', text:'#0D1137', shape:'shield', icon:'school', title:'Degree', subtitle:'Cum Laude', year:'2026', gradient:false, laurel:true, ribbon:false, stars:true, pattern:false },
        modern:    { primary:'#7C4DFF', secondary:'#651FFF', bg:'#EDE7F6', text:'#311B92', shape:'rounded', icon:'diamond', title:'Specialist', subtitle:'Verified', year:'2026', gradient:true, laurel:false, ribbon:false, stars:false, pattern:false },
        minimal:   { primary:'#37474F', secondary:'#78909C', bg:'#FFFFFF', text:'#212121', shape:'circle', icon:'verified', title:'Verified', subtitle:'', year:'', gradient:false, laurel:false, ribbon:false, stars:false, pattern:false },
        neon:      { primary:'#00E676', secondary:'#76FF03', bg:'#1A1A2E', text:'#EAEAEA', shape:'hex', icon:'auto_awesome', title:'Elite', subtitle:'Certified', year:'2026', gradient:true, laurel:false, ribbon:false, stars:true, pattern:true },
        corporate: { primary:'#1565C0', secondary:'#0D47A1', bg:'#F5F5F5', text:'#212121', shape:'rounded', icon:'handshake', title:'Partner', subtitle:'Certified', year:'2026', gradient:false, laurel:false, ribbon:true, stars:false, pattern:false },
        creative:  { primary:'#E91E63', secondary:'#9C27B0', bg:'#FCE4EC', text:'#4A148C', shape:'star', icon:'brush', title:'Creative', subtitle:'Master', year:'2026', gradient:true, laurel:false, ribbon:false, stars:true, pattern:false },
        fire:      { primary:'#FF5722', secondary:'#FF9800', bg:'#FBE9E7', text:'#BF360C', shape:'diamond', icon:'local_fire_department', title:'Outstanding', subtitle:'Top Performer', year:'2026', gradient:true, laurel:false, ribbon:false, stars:true, pattern:false },
        science:   { primary:'#00897B', secondary:'#26A69A', bg:'#E0F2F1', text:'#004D40', shape:'octagon', icon:'science', title:'Research', subtitle:'Excellence', year:'2026', gradient:false, laurel:true, ribbon:false, stars:false, pattern:true },
    };

    const iconMap = {
        star:'★', verified:'✓', workspace_premium:'◆', military_tech:'⚔', school:'🎓',
        emoji_events:'🏆', thumb_up:'👍', rocket_launch:'🚀', bolt:'⚡', diamond:'💎',
        favorite:'♥', shield:'🛡', psychology:'🧠', code:'</>',  science:'⚗',
        auto_awesome:'✨', local_fire_department:'🔥', music_note:'♪', brush:'🎨',
        fitness_center:'💪', public:'🌍', lightbulb:'💡', engineering:'⚙', biotech:'🧬',
        grade:'⭐', celebration:'🎉', handshake:'🤝', groups:'👥', trending_up:'📈', token:'⬡'
    };

    function getVal(id) { return document.getElementById(id)?.value ?? ''; }
    function isChecked(id) { return document.getElementById(id)?.checked ?? false; }

    function renderBadge() {
        const size = parseInt(getVal('badgeSize'));
        const primary = getVal('primaryColor');
        const secondary = getVal('secondaryColor');
        const bg = getVal('bgColor');
        const textCol = getVal('textColor');
        const title = getVal('badgeTitle') || 'Badge';
        const subtitle = getVal('badgeSubtitle');
        const year = getVal('badgeYear');
        const shape = document.querySelector('input[name="shape"]:checked')?.value || 'circle';
        const border = getVal('borderStyle');
        const bw = parseInt(getVal('borderWidth') || 3);
        const font = getVal('badgeFont') || 'sans-serif';
        const bold = isChecked('textBold') ? 'bold' : 'normal';
        const rotation = parseInt(getVal('badgeRotation') || 0);
        const iconScale = parseInt(getVal('iconSize') || 100) / 100;
        const showIconFlag = isChecked('showIcon');
        const useShadow = isChecked('textShadow');
        const dropShadow = isChecked('showDropShadow');
        const innerGlow = isChecked('showInnerGlow');
        const showStars = isChecked('showStars');
        const showLaurel = isChecked('showLaurel');
        const showRibbon = isChecked('showRibbon');
        const showPattern = isChecked('showPattern');
        const patternType = getVal('patternType');
        const opacity = parseInt(getVal('bgOpacity') || 100) / 100;
        const useGrad = isChecked('useGradient');
        const borderCol = isChecked('borderMatchPrimary') ? primary : getVal('borderColor');

        const cx = size/2, cy = size/2, r = size/2 - 12;
        let defs = '';
        let svg = '';

        // Gradient definition
        if (useGrad) {
            const gs = getVal('gradStart');
            const ge = getVal('gradEnd');
            const dir = getVal('gradDirection');
            if (dir === 'radial') {
                defs += `<radialGradient id="bgGrad" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="${gs}"/><stop offset="100%" stop-color="${ge}"/></radialGradient>`;
            } else {
                const coords = dir === 'lr' ? 'x1="0" y1="0" x2="1" y2="0"' : dir === 'diag' ? 'x1="0" y1="0" x2="1" y2="1"' : 'x1="0" y1="0" x2="0" y2="1"';
                defs += `<linearGradient id="bgGrad" ${coords}><stop offset="0%" stop-color="${gs}"/><stop offset="100%" stop-color="${ge}"/></linearGradient>`;
            }
        }

        // Pattern definition
        if (showPattern) {
            if (patternType === 'dots') {
                defs += `<pattern id="bgPat" width="12" height="12" patternUnits="userSpaceOnUse"><circle cx="6" cy="6" r="1.5" fill="${primary}" opacity="0.1"/></pattern>`;
            } else if (patternType === 'lines') {
                defs += `<pattern id="bgPat" width="8" height="8" patternUnits="userSpaceOnUse"><line x1="0" y1="0" x2="8" y2="8" stroke="${primary}" stroke-width="0.5" opacity="0.1"/></pattern>`;
            } else if (patternType === 'crosshatch') {
                defs += `<pattern id="bgPat" width="8" height="8" patternUnits="userSpaceOnUse"><line x1="0" y1="0" x2="8" y2="8" stroke="${primary}" stroke-width="0.5" opacity="0.08"/><line x1="8" y1="0" x2="0" y2="8" stroke="${primary}" stroke-width="0.5" opacity="0.08"/></pattern>`;
            } else {
                defs += `<pattern id="bgPat" width="20" height="10" patternUnits="userSpaceOnUse"><path d="M0 5 Q5 0 10 5 Q15 10 20 5" fill="none" stroke="${primary}" stroke-width="0.5" opacity="0.1"/></pattern>`;
            }
        }

        // Drop shadow filter
        if (dropShadow) {
            defs += `<filter id="dropshadow"><feDropShadow dx="2" dy="3" stdDeviation="4" flood-color="#000" flood-opacity="0.25"/></filter>`;
        }
        // Inner glow filter
        if (innerGlow) {
            defs += `<filter id="innerglow"><feFlood flood-color="${secondary}" result="flood"/><feComposite in="flood" in2="SourceGraphic" operator="out" result="comp"/><feGaussianBlur in="comp" stdDeviation="4" result="blur"/><feComposite in="blur" in2="SourceGraphic" operator="atop"/></filter>`;
        }
        // Text shadow filter
        if (useShadow) {
            defs += `<filter id="txtshadow"><feDropShadow dx="1" dy="1" stdDeviation="1" flood-color="#000" flood-opacity="0.3"/></filter>`;
        }

        const fillColor = useGrad ? 'url(#bgGrad)' : bg;
        const strokeW = border === 'none' ? 0 : bw;
        let dashArr = '';
        if (border === 'dashed') dashArr = `stroke-dasharray="${bw*3},${bw*2}"`;
        if (border === 'dotted') dashArr = `stroke-dasharray="${bw},${bw}"`;
        const filterAttr = dropShadow ? 'filter="url(#dropshadow)"' : '';
        const innerFilterAttr = innerGlow ? 'filter="url(#innerglow)"' : '';

        // Shape
        const shapeGroup = rotation ? `<g transform="rotate(${rotation} ${cx} ${cy})">` : '<g>';
        
        if (shape === 'circle') {
            svg += `${shapeGroup}<circle cx="${cx}" cy="${cy}" r="${r}" fill="${fillColor}" fill-opacity="${opacity}" stroke="${borderCol}" stroke-width="${strokeW}" ${dashArr} ${filterAttr}/>`;
            if (border === 'double') svg += `<circle cx="${cx}" cy="${cy}" r="${r - bw*3}" fill="none" stroke="${borderCol}" stroke-width="${Math.max(1,bw-1)}"/>`;
            svg += '</g>';
        } else if (shape === 'rounded') {
            const m = 12;
            svg += `${shapeGroup}<rect x="${m}" y="${m}" width="${size-m*2}" height="${size-m*2}" rx="20" fill="${fillColor}" fill-opacity="${opacity}" stroke="${borderCol}" stroke-width="${strokeW}" ${dashArr} ${filterAttr}/>`;
            if (border === 'double') svg += `<rect x="${m+bw*3}" y="${m+bw*3}" width="${size-m*2-bw*6}" height="${size-m*2-bw*6}" rx="14" fill="none" stroke="${borderCol}" stroke-width="${Math.max(1,bw-1)}"/>`;
            svg += '</g>';
        } else if (shape === 'hex') {
            const pts = []; for (let i=0;i<6;i++){const a=(Math.PI/3)*i-Math.PI/6;pts.push(`${cx+r*Math.cos(a)},${cy+r*Math.sin(a)}`);}
            svg += `${shapeGroup}<polygon points="${pts.join(' ')}" fill="${fillColor}" fill-opacity="${opacity}" stroke="${borderCol}" stroke-width="${strokeW}" ${dashArr} ${filterAttr}/></g>`;
        } else if (shape === 'shield') {
            const m = 15;
            svg += `${shapeGroup}<path d="M${cx},${m} L${size-m},${size*0.3} L${size-m},${size*0.6} Q${size-m},${size-m} ${cx},${size-m} Q${m},${size-m} ${m},${size*0.6} L${m},${size*0.3} Z" fill="${fillColor}" fill-opacity="${opacity}" stroke="${borderCol}" stroke-width="${strokeW}" ${filterAttr}/></g>`;
        } else if (shape === 'star') {
            const pts = []; for(let i=0;i<10;i++){const a=(Math.PI/5)*i-Math.PI/2;const rad=i%2===0?r:r*0.5;pts.push(`${cx+rad*Math.cos(a)},${cy+rad*Math.sin(a)}`);}
            svg += `${shapeGroup}<polygon points="${pts.join(' ')}" fill="${fillColor}" fill-opacity="${opacity}" stroke="${borderCol}" stroke-width="${strokeW}" ${filterAttr}/></g>`;
        } else if (shape === 'diamond') {
            svg += `${shapeGroup}<polygon points="${cx},${12} ${size-12},${cy} ${cx},${size-12} ${12},${cy}" fill="${fillColor}" fill-opacity="${opacity}" stroke="${borderCol}" stroke-width="${strokeW}" ${filterAttr}/></g>`;
        } else if (shape === 'octagon') {
            const pts = []; for(let i=0;i<8;i++){const a=(Math.PI/4)*i-Math.PI/8;pts.push(`${cx+r*Math.cos(a)},${cy+r*Math.sin(a)}`);}
            svg += `${shapeGroup}<polygon points="${pts.join(' ')}" fill="${fillColor}" fill-opacity="${opacity}" stroke="${borderCol}" stroke-width="${strokeW}" ${filterAttr}/></g>`;
        } else if (shape === 'ribbon') {
            const w = size*0.8, h = size*0.7, lx=(size-w)/2, ly=size*0.1;
            svg += `${shapeGroup}<path d="M${lx},${ly} H${lx+w} V${ly+h} L${cx},${ly+h+size*0.15} L${lx},${ly+h} Z" fill="${fillColor}" fill-opacity="${opacity}" stroke="${borderCol}" stroke-width="${strokeW}" ${filterAttr}/></g>`;
        }

        // Pattern overlay
        if (showPattern) {
            if (shape === 'circle') svg += `<circle cx="${cx}" cy="${cy}" r="${r-2}" fill="url(#bgPat)"/>`;
            else svg += `<rect x="0" y="0" width="${size}" height="${size}" fill="url(#bgPat)"/>`;
        }

        // Laurel wreath
        if (showLaurel) {
            const lr = r * 0.85;
            for (let side = -1; side <= 1; side += 2) {
                for (let i = 0; i < 6; i++) {
                    const angle = (Math.PI * 0.3) + (Math.PI * 0.06 * i);
                    const x = cx + side * lr * 0.5 * Math.sin(angle);
                    const y = cy + lr * 0.4 * Math.cos(angle) - lr * 0.1;
                    svg += `<ellipse cx="${x}" cy="${y}" rx="${size*0.025}" ry="${size*0.05}" fill="${secondary}" opacity="0.25" transform="rotate(${side*20+i*side*8} ${x} ${y})"/>`;
                }
            }
        }

        // Accent stars
        if (showStars) {
            const starY = cy + size * 0.28;
            for (let i = -1; i <= 1; i++) {
                const sx = cx + i * size * 0.12;
                const ss = size * (i === 0 ? 0.035 : 0.025);
                svg += `<polygon points="${sx},${starY-ss} ${sx+ss*0.4},${starY-ss*0.3} ${sx+ss},${starY-ss*0.2} ${sx+ss*0.5},${starY+ss*0.2} ${sx+ss*0.6},${starY+ss} ${sx},${starY+ss*0.5} ${sx-ss*0.6},${starY+ss} ${sx-ss*0.5},${starY+ss*0.2} ${sx-ss},${starY-ss*0.2} ${sx-ss*0.4},${starY-ss*0.3}" fill="${secondary}" opacity="0.7"/>`;
            }
        }

        // Icon
        const txtFilter = useShadow ? 'filter="url(#txtshadow)"' : '';
        if (showIconFlag) {
            const iSize = size * 0.28 * iconScale;
            const ix = cx - iSize/2;
            const iy = cy - size*0.18 - iSize/2;
            if (iconSource === 'upload' && customIconDataUrl) {
                // Embed uploaded image in SVG
                svg += `<image href="${customIconDataUrl}" x="${ix}" y="${iy}" width="${iSize}" height="${iSize}" preserveAspectRatio="xMidYMid meet" ${txtFilter}/>`;
            } else {
                const iTextSize = size * 0.16 * iconScale;
                svg += `<text x="${cx}" y="${cy - size*0.04}" text-anchor="middle" font-size="${iTextSize}" fill="${primary}" ${txtFilter} dominant-baseline="central">${iconMap[selectedIcon] || '✓'}</text>`;
            }
        }

        // Title
        svg += `<text x="${cx}" y="${cy + size*0.14}" text-anchor="middle" font-size="${size*0.075}" font-weight="${bold}" fill="${textCol}" font-family="${font}" ${txtFilter}>${esc(title)}</text>`;

        // Subtitle
        if (subtitle) {
            svg += `<text x="${cx}" y="${cy + size*0.23}" text-anchor="middle" font-size="${size*0.05}" fill="${textCol}" font-family="${font}" opacity="0.7" ${txtFilter}>${esc(subtitle)}</text>`;
        }

        // Year
        if (year) {
            svg += `<text x="${cx}" y="${cy + size*0.33}" text-anchor="middle" font-size="${size*0.04}" fill="${textCol}" font-family="${font}" opacity="0.5" ${txtFilter}>${esc(year)}</text>`;
        }

        // Bottom ribbon
        if (showRibbon) {
            const rw = size * 0.5, rh = size * 0.08;
            const rx = cx - rw/2, ry = cy + size * 0.35;
            svg += `<rect x="${rx}" y="${ry}" width="${rw}" height="${rh}" rx="4" fill="${primary}" opacity="0.9"/>`;
            svg += `<text x="${cx}" y="${ry + rh*0.72}" text-anchor="middle" font-size="${rh*0.55}" fill="#fff" font-family="${font}" font-weight="bold">${esc(title)}</text>`;
        }

        const fullSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}"><defs>${defs}</defs>${svg}</svg>`;
        preview.innerHTML = fullSvg;

        // Update labels
        document.getElementById('sizeLabel').textContent = size;
        document.getElementById('rotLabel').textContent = rotation;
        document.getElementById('iconSizeLabel').textContent = getVal('iconSize');
        document.getElementById('borderWidthLabel').textContent = bw;
    }

    function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    // --- Event Listeners ---
    const inputs = ['badgeSize','primaryColor','secondaryColor','bgColor','textColor','badgeTitle','badgeSubtitle','badgeYear','borderStyle','borderWidth','borderColor','badgeFont','badgeRotation','iconSize','bgOpacity','gradStart','gradEnd','gradDirection','patternType'];
    inputs.forEach(id => { document.getElementById(id)?.addEventListener('input', renderBadge); });

    const checks = ['textBold','textShadow','showDropShadow','showInnerGlow','showStars','showLaurel','showRibbon','showPattern','showIcon','useGradient','borderMatchPrimary'];
    checks.forEach(id => {
        document.getElementById(id)?.addEventListener('change', function() {
            if (id === 'useGradient') document.getElementById('gradientSettings').style.display = this.checked ? '' : 'none';
            if (id === 'borderMatchPrimary') document.getElementById('borderColor').disabled = this.checked;
            renderBadge();
        });
    });

    document.querySelectorAll('input[name="shape"]').forEach(r => r.addEventListener('change', renderBadge));

    document.querySelectorAll('.icon-picker').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.icon-picker').forEach(b => { b.classList.remove('btn-primary'); b.classList.add('btn-outline-secondary'); });
            this.classList.add('btn-primary'); this.classList.remove('btn-outline-secondary');
            selectedIcon = this.dataset.icon;
            renderBadge();
        });
    });

    // Icon source toggle
    document.querySelectorAll('input[name="iconSource"]').forEach(radio => {
        radio.addEventListener('change', function() {
            iconSource = this.value;
            document.getElementById('builtinIconPanel').style.display = iconSource === 'builtin' ? '' : 'none';
            document.getElementById('uploadIconPanel').style.display = iconSource === 'upload' ? '' : 'none';
            renderBadge();
        });
    });

    // Custom icon file upload
    document.getElementById('customIconFile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('Image is too large. Maximum size is 2MB.');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(ev) {
            customIconDataUrl = ev.target.result;
            // Show preview
            const preview = document.getElementById('customIconPreview');
            preview.innerHTML = `<img src="${customIconDataUrl}" style="width:100%;height:100%;object-fit:contain;border-radius:6px" alt="icon">`;
            document.getElementById('clearCustomIcon').style.display = '';
            renderBadge();
        };
        reader.readAsDataURL(file);
    });

    // Clear custom icon
    document.getElementById('clearCustomIcon').addEventListener('click', function() {
        customIconDataUrl = null;
        document.getElementById('customIconFile').value = '';
        document.getElementById('customIconPreview').innerHTML = '<span class="material-symbols-rounded text-muted" style="font-size:28px">image</span>';
        this.style.display = 'none';
        renderBadge();
    });

    // Presets
    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const p = presets[this.dataset.preset]; if (!p) return;
            document.getElementById('primaryColor').value = p.primary;
            document.getElementById('secondaryColor').value = p.secondary;
            document.getElementById('bgColor').value = p.bg;
            document.getElementById('textColor').value = p.text;
            document.getElementById('badgeTitle').value = p.title;
            document.getElementById('badgeSubtitle').value = p.subtitle;
            document.getElementById('badgeYear').value = p.year;
            selectedIcon = p.icon;
            document.querySelector(`input[name="shape"][value="${p.shape}"]`).checked = true;
            document.getElementById('useGradient').checked = p.gradient;
            document.getElementById('gradientSettings').style.display = p.gradient ? '' : 'none';
            document.getElementById('showLaurel').checked = p.laurel;
            document.getElementById('showRibbon').checked = p.ribbon;
            document.getElementById('showStars').checked = p.stars;
            document.getElementById('showPattern').checked = p.pattern;
            // Highlight icon
            document.querySelectorAll('.icon-picker').forEach(b => { b.classList.remove('btn-primary'); b.classList.add('btn-outline-secondary'); });
            document.querySelector(`.icon-picker[data-icon="${p.icon}"]`)?.classList.add('btn-primary');
            document.querySelector(`.icon-picker[data-icon="${p.icon}"]`)?.classList.remove('btn-outline-secondary');
            renderBadge();
        });
    });

    // Color palettes
    document.querySelectorAll('.palette-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('primaryColor').value = this.dataset.primary;
            document.getElementById('secondaryColor').value = this.dataset.secondary;
            document.getElementById('bgColor').value = this.dataset.bg;
            document.getElementById('textColor').value = this.dataset.text;
            renderBadge();
        });
    });

    // Preview backgrounds
    document.querySelectorAll('.bg-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.bg-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const c = document.getElementById('previewContainer');
            const mode = this.dataset.bg;
            if (mode === 'dark') c.style.background = '#1a1a2e';
            else if (mode === 'light') c.style.background = '#f8f9fa';
            else c.style.background = 'repeating-conic-gradient(#f0f0f0 0% 25%, white 0% 50%) 50% / 20px 20px';
        });
    });

    // Save
    document.getElementById('saveBadge').addEventListener('click', function() {
        const svgEl = preview.innerHTML;
        const name = getVal('badgeName');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        this.disabled = true;
        fetch('<?= url('badge/save') ?>', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN':csrf},
            body:`_csrf_token=${encodeURIComponent(csrf)}&badge_name=${encodeURIComponent(name)}&svg_data=${encodeURIComponent(svgEl)}`
        }).then(r=>r.json()).then(data => {
            this.disabled = false;
            if (data.success) alert('Badge saved successfully!');
            else alert(data.error || 'Failed to save.');
        }).catch(() => { this.disabled = false; alert('Network error.'); });
    });

    // Download SVG
    document.getElementById('downloadBadge').addEventListener('click', function() {
        const svgEl = preview.innerHTML;
        const blob = new Blob([svgEl], {type:'image/svg+xml'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a'); a.href=url; a.download=(getVal('badgeName')||'badge')+'.svg'; a.click();
        URL.revokeObjectURL(url);
    });

    // Download PNG
    document.getElementById('downloadPng').addEventListener('click', function() {
        const svgEl = preview.querySelector('svg');
        if (!svgEl) return;
        const canvas = document.createElement('canvas');
        const size = parseInt(svgEl.getAttribute('width')) * 2;
        canvas.width = size; canvas.height = size;
        const ctx = canvas.getContext('2d');
        const xml = new XMLSerializer().serializeToString(svgEl);
        const img = new Image();
        img.onload = function() {
            ctx.drawImage(img, 0, 0, size, size);
            const a = document.createElement('a');
            a.href = canvas.toDataURL('image/png');
            a.download = (getVal('badgeName')||'badge') + '.png';
            a.click();
        };
        img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(xml)));
    });

    renderBadge();
});
</script>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
