<?php
$assetPath = "../../assets/";
include '../../layouts/header.php';

// Koneksi database
require_once '../../../config/database.php';

// Fetch all subjects with tutor count based on keahlian
$query = "SELECT t.keahlian, COUNT(DISTINCT t.id) as tutor_count,
          GROUP_CONCAT(DISTINCT tm.jenjang ORDER BY tm.jenjang SEPARATOR ', ') as jenjang_list
          FROM tutor t
          LEFT JOIN tutor_mapel tm ON t.id = tm.tutor_id
          WHERE t.status = 'Aktif'
          GROUP BY t.keahlian
          ORDER BY tutor_count DESC, t.keahlian ASC";

$result = mysqli_query($conn, $query);
$allSubjects = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $jenjangList = $row['jenjang_list'] ? explode(', ', $row['jenjang_list']) : ['Semua Jenjang'];
        
        $allSubjects[] = [
            'subject_name' => $row['keahlian'],
            'tutor_count' => $row['tutor_count'],
            'jenjang_list' => !empty($jenjangList) ? implode(', ', array_unique($jenjangList)) : 'Semua Jenjang'
        ];
    }
}

// Kategorisasi mata pelajaran
$categories = [
    'Eksak' => ['Matematika', 'Fisika', 'Kimia'],
    'Ilmu Alam' => ['Biologi', 'Geografi'],
    'Bahasa' => ['Bahasa Indonesia', 'Bahasa Inggris', 'Bahasa Jawa', 'Bahasa Arab'],
    'Sosial' => ['Ekonomi', 'Sosiologi', 'Sejarah', 'PKN', 'PPKN'],
    'Teknologi' => ['Pemrograman', 'TIK', 'Informatika'],
    'Seni & Lainnya' => []
];

// Assign subjects to categories
$categorizedSubjects = [];
foreach ($categories as $catName => $keywords) {
    $categorizedSubjects[$catName] = [];
}

foreach ($allSubjects as $subject) {
    $assigned = false;
    foreach ($categories as $catName => $keywords) {
        foreach ($keywords as $keyword) {
            if (stripos($subject['subject_name'], $keyword) !== false) {
                $categorizedSubjects[$catName][] = $subject;
                $assigned = true;
                break 2;
            }
        }
    }
    if (!$assigned) {
        $categorizedSubjects['Seni & Lainnya'][] = $subject;
    }
}

// Icon mapping for subjects
$iconMap = [
    'Matematika' => 'bi-calculator',
    'Fisika' => 'bi-lightning',
    'Kimia' => 'bi-droplet',
    'Biologi' => 'bi-heart-pulse',
    'Bahasa Inggris' => 'bi-translate',
    'Pemrograman' => 'bi-code-slash',
    'Bahasa Indonesia' => 'bi-book',
    'Geografi' => 'bi-globe',
    'Ekonomi' => 'bi-graph-up',
    'Sejarah' => 'bi-clock-history',
    'TIK' => 'bi-laptop',
    'PKN' => 'bi-flag',
    'PPKN' => 'bi-flag',
    'Sosiologi' => 'bi-people',
    'Informatika' => 'bi-cpu'
];

// Color schemes for categories
$colorSchemes = [
    'Eksak' => ['start' => '#667eea', 'end' => '#764ba2'],
    'Ilmu Alam' => ['start' => '#43e97b', 'end' => '#38f9d7'],
    'Bahasa' => ['start' => '#fa709a', 'end' => '#fee140'],
    'Sosial' => ['start' => '#ff9a9e', 'end' => '#fecfef'],
    'Teknologi' => ['start' => '#30cfd0', 'end' => '#330867'],
    'Seni & Lainnya' => ['start' => '#a8edea', 'end' => '#fed6e3']
];
?>
<style>
    body {
        background: #f8f9fa;
    }

    .content-section {
        padding: 60px 0;
    }

        .category-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a5f7a;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #FF6B35;
            display: inline-block;
        }

        .subject-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 60px;
        }

        .subject-card {
            background: white;
            padding: 30px 25px;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }

        .subject-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
            transform: scaleX(0);
            transition: transform 0.3s;
        }

        .subject-card:hover::before {
            transform: scaleX(1);
        }

        .subject-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .subject-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .subject-icon i {
            font-size: 32px;
            color: white;
        }

        .subject-name {
            font-size: 20px;
            font-weight: 700;
            color: #1a5f7a;
            margin-bottom: 10px;
        }

        .subject-info {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            color: #666;
        }

        .info-item i {
            color: #FF6B35;
        }

        .tutor-count {
            font-size: 15px;
            color: #FF6B35;
            font-weight: 600;
        }

        .jenjang-badges {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .badge-jenjang {
            padding: 4px 12px;
            background: #f0f0f0;
            border-radius: 20px;
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }

        .stats-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            text-align: center;
        }

        .stat-item {
            padding: 20px;
        }

        .stat-number {
            font-size: 48px;
            font-weight: 800;
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 16px;
            color: #666;
            margin-top: 10px;
        }

        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .filter-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .filter-btn {
            padding: 10px 25px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            color: #666;
        }

        .filter-btn:hover, .filter-btn.active {
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            color: white;
            border-color: #FF6B35;
        }

        @media (max-width: 768px) {
            .subject-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

<!-- CONTAINER -->
<div class="container">
    <!-- Search Box -->
    <div class="search-box" role="search" aria-label="Pencarian mata pelajaran" style="max-width: 700px; margin: 40px auto; position: relative;">
        <input type="text" id="searchInput" placeholder="Cari mata pelajaran..." onkeyup="searchSubjects()" 
               style="width: 100%; padding: 18px 60px 18px 25px; border: 2px solid #e0e0e0; border-radius: 50px; font-size: 16px;">
        <button onclick="searchSubjects()" 
                style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); border: none; padding: 12px 25px; border-radius: 50px; color: white; font-weight: 600; cursor: pointer;">
            <i class="bi bi-search"></i> Cari
        </button>
    </div>

    <!-- Stats Section -->
    <div class="content-section">
        <div class="stats-section" style="background: white; padding: 40px; border-radius: 20px; margin-bottom: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; text-align: center;">
                <div class="stat-item" style="padding: 20px;">
                    <div class="stat-number" style="font-size: 48px; font-weight: 800; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"><?php echo count($allSubjects); ?>+</div>
                    <div class="stat-label" style="font-size: 16px; color: #666; margin-top: 10px;">Mata Pelajaran</div>
                </div>
                <div class="stat-item" style="padding: 20px;">
                    <div class="stat-number" style="font-size: 48px; font-weight: 800; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                        <?php 
                        $totalTutors = 0;
                        foreach ($allSubjects as $s) {
                            $totalTutors += $s['tutor_count'];
                        }
                        echo $totalTutors;
                        ?>+
                    </div>
                    <div class="stat-label" style="font-size: 16px; color: #666; margin-top: 10px;">Tutor Berpengalaman</div>
                </div>
                <div class="stat-item" style="padding: 20px;">
                    <div class="stat-number" style="font-size: 48px; font-weight: 800; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">3</div>
                    <div class="stat-label" style="font-size: 16px; color: #666; margin-top: 10px;">Jenjang Pendidikan</div>
                </div>
                <div class="stat-item" style="padding: 20px;">
                    <div class="stat-number" style="font-size: 48px; font-weight: 800; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">24/7</div>
                    <div class="stat-label" style="font-size: 16px; color: #666; margin-top: 10px;">Layanan Tersedia</div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section" style="background: white; padding: 25px; border-radius: 15px; margin-bottom: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
            <div class="filter-buttons" style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: center;">
                <button class="filter-btn active" onclick="filterCategory('all')" style="padding: 10px 25px; border: 2px solid #ddd; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); color: white; border-color: #FF6B35; border-radius: 50px; cursor: pointer; transition: all 0.3s; font-weight: 600;">Semua</button>
                <button class="filter-btn" onclick="filterCategory('Eksak')" style="padding: 10px 25px; border: 2px solid #ddd; background: white; border-radius: 50px; cursor: pointer; transition: all 0.3s; font-weight: 600; color: #666;">Eksak</button>
                <button class="filter-btn" onclick="filterCategory('Ilmu Alam')" style="padding: 10px 25px; border: 2px solid #ddd; background: white; border-radius: 50px; cursor: pointer; transition: all 0.3s; font-weight: 600; color: #666;">Ilmu Alam</button>
                <button class="filter-btn" onclick="filterCategory('Bahasa')" style="padding: 10px 25px; border: 2px solid #ddd; background: white; border-radius: 50px; cursor: pointer; transition: all 0.3s; font-weight: 600; color: #666;">Bahasa</button>
                <button class="filter-btn" onclick="filterCategory('Sosial')" style="padding: 10px 25px; border: 2px solid #ddd; background: white; border-radius: 50px; cursor: pointer; transition: all 0.3s; font-weight: 600; color: #666;">Sosial</button>
                <button class="filter-btn" onclick="filterCategory('Teknologi')" style="padding: 10px 25px; border: 2px solid #ddd; background: white; border-radius: 50px; cursor: pointer; transition: all 0.3s; font-weight: 600; color: #666;">Teknologi</button>
                <button class="filter-btn" onclick="filterCategory('Seni & Lainnya')" style="padding: 10px 25px; border: 2px solid #ddd; background: white; border-radius: 50px; cursor: pointer; transition: all 0.3s; font-weight: 600; color: #666;">Lainnya</button>
            </div>
        </div>

        <!-- Subjects by Category -->
        <?php foreach ($categorizedSubjects as $catName => $subjects): ?>
            <?php if (count($subjects) > 0): ?>
                <div class="category-section" data-category="<?php echo htmlspecialchars($catName); ?>">
                    <h2 class="category-title"><?php echo htmlspecialchars($catName); ?></h2>
                    
                    <div class="subject-grid">
                        <?php foreach ($subjects as $subject): ?>
                            <?php
                            $mapel = $subject['subject_name'];
                            $count = $subject['tutor_count'];
                            $jenjangList = explode(', ', $subject['jenjang_list']);
                            
                            // Find matching icon
                            $icon = 'bi-journal-text';
                            foreach ($iconMap as $key => $val) {
                                if (stripos($mapel, $key) !== false) {
                                    $icon = $val;
                                    break;
                                }
                            }
                            
                            // Get color scheme for category
                            $colorStart = $colorSchemes[$catName]['start'];
                            $colorEnd = $colorSchemes[$catName]['end'];
                            ?>
                            <div class="subject-card" 
                                 style="--gradient-start: <?php echo $colorStart; ?>; --gradient-end: <?php echo $colorEnd; ?>;"
                                 onclick="window.location.href='search_result.php?keyword=<?php echo urlencode($mapel); ?>'">
                                <div class="subject-icon">
                                    <i class="bi <?php echo $icon; ?>"></i>
                                </div>
                                <div class="subject-name"><?php echo htmlspecialchars($mapel); ?></div>
                                <div class="tutor-count"><?php echo $count; ?>+ Tutor Tersedia</div>
                                
                                <div class="jenjang-badges">
                                    <?php foreach (array_unique($jenjangList) as $jenjang): ?>
                                        <span class="badge-jenjang"><?php echo htmlspecialchars($jenjang); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    </div>
</div>

<!-- Footer -->
<?php include '../../layouts/footer.php'; ?>

<script>
    function searchSubjects() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.subject-card');
            const sections = document.querySelectorAll('.category-section');
            
            let hasResults = false;
            
            sections.forEach(section => {
                let sectionHasResults = false;
                const sectionCards = section.querySelectorAll('.subject-card');
                
                sectionCards.forEach(card => {
                    const name = card.querySelector('.subject-name').textContent.toLowerCase();
                    if (name.includes(input)) {
                        card.style.display = 'block';
                        sectionHasResults = true;
                        hasResults = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                section.style.display = sectionHasResults ? 'block' : 'none';
            });
        }

        function filterCategory(category) {
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Filter sections
            const sections = document.querySelectorAll('.category-section');
            sections.forEach(section => {
                if (category === 'all') {
                    section.style.display = 'block';
                } else {
                    section.style.display = section.dataset.category === category ? 'block' : 'none';
                }
            });
            
            // Reset search
            document.getElementById('searchInput').value = '';
            document.querySelectorAll('.subject-card').forEach(card => {
                card.style.display = 'block';
            });
        }
        
        // Update filter button styles
        function filterCategory(category) {
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.style.background = 'white';
                btn.style.color = '#666';
                btn.style.borderColor = '#ddd';
            });
            event.target.style.background = 'linear-gradient(135deg, #FF6B35 0%, #F7931E 100%)';
            event.target.style.color = 'white';
            event.target.style.borderColor = '#FF6B35';
            
            // Filter sections
            const sections = document.querySelectorAll('.category-section');
            sections.forEach(section => {
                if (category === 'all') {
                    section.style.display = 'block';
                } else {
                    section.style.display = section.dataset.category === category ? 'block' : 'none';
                }
            });
            
            // Reset search
            document.getElementById('searchInput').value = '';
            document.querySelectorAll('.subject-card').forEach(card => {
                card.style.display = 'block';
            });
        }
    </script>
