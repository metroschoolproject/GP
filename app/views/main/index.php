<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APPNAME ?></title>
    <?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
    <?php $indexCssVersion = file_exists(APPROOT . '/../public/css/index.css') ? filemtime(APPROOT . '/../public/css/index.css') : time(); ?>
    <link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
    <link rel="stylesheet" href="<?= URLROOT ?>/public/css/index.css?v=<?= $indexCssVersion ?>">
</head>
<body class="home-body">
    

    <header class="home-header">
        <a class="home-brand" href="<?= URLROOT ?>/main/home" aria-label="Golden Promise home">
            <span class="brand-symbol">GP</span>
            <span>Golden Promise</span>
        </a>
        <div class="home-actions" aria-label="Account links">
            <a href="<?= URLROOT ?>/login">Login</a>
            <a href="<?= URLROOT ?>/users/register?type=supplier">Be our partner</a>
        </div>
    </header>


<!-- 
    <main class="home-page">
        <section class="kunumi-hero" aria-labelledby="home-title">
            <canvas class="hero-field" id="promiseField" aria-hidden="true"></canvas>

            <div class="hero-intro">
                <p class="hero-kicker">A wedding marketplace for a beautiful next chapter</p>
                <h2 id="home-title" style="font-size: 30px;">New Chapter Begin</h2>
            </div>

  

            <div class="hero-footer">
                <p>Plan with the people, places, and details that make your day feel like yours.</p>

                <button class="menu-toggle" type="button" aria-label="Open navigation menu">
                    <span>Menu</span>
                    <i></i>
                </button>

                <nav class="main-nav" aria-label="Main navigation">
                    <a href="#venue">Venue</a>
                    <a href="#dress">Dress</a>
                    <a href="#studio">Studio</a>
                    <a href="#food">Food</a>
                    <a href="#accessories">Accessories</a>
                    <a href="#packages">Packages</a>
                </nav>
            </div>
        </section>

        <section class="service-strip" aria-label="Wedding services">
            <a id="venue" class="service-item" href="#venue">
                <span>01</span>
                <strong>Venue</strong>
                <em>ceremony and reception spaces</em>
            </a>
            <a id="dress" class="service-item" href="#dress">
                <span>02</span>
                <strong>Dress</strong>
                <em>gowns, suits, and fittings</em>
            </a>
            <a id="studio" class="service-item" href="#studio">
                <span>03</span>
                <strong>Studio</strong>
                <em>photo, video, and memories</em>
            </a>
            <a id="food" class="service-item" href="#food">
                <span>04</span>
                <strong>Food</strong>
                <em>menus, cakes, and catering</em>
            </a>
            <a id="accessories" class="service-item" href="#accessories">
                <span>05</span>
                <strong>Accessories</strong>
                <em>rings, florals, and finishing touches</em>
            </a>
            <a id="packages" class="service-item" href="#packages">
                <span>06</span>
                <strong>Packages</strong>
                <em>ready-made plans for every scale</em>
            </a>
        </section>

        <section id="partner" class="partner-panel" aria-label="Partner invitation">
            <p>For venues, studios, boutiques, caterers, and wedding teams.</p>
            <a href="<?= URLROOT ?>/users/register?type=supplier">Join Golden Promise</a>
        </section>
    </main>

    <script>
        const toggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('.main-nav');

        if (toggle && nav) {
            toggle.addEventListener('click', () => {
                nav.classList.toggle('open');
                toggle.classList.toggle('active');
            });

            nav.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => {
                    nav.classList.remove('open');
                    toggle.classList.remove('active');
                });
            });
        }

        const canvas = document.getElementById('promiseField');
        const hero = document.querySelector('.kunumi-hero');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        const photoSources = [
            'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=480&q=80',
            'https://images.unsplash.com/photo-1465495976277-4387d4b0e4a6?auto=format&fit=crop&w=480&q=80',
            'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=480&q=80',
            'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=480&q=80',
            'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=480&q=80',
            'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=480&q=80'
        ];

        if (canvas && hero) {
            const context = canvas.getContext('2d');
            const nodes = [];
            const photos = photoSources.map((source) => {
                const image = new Image();
                image.crossOrigin = 'anonymous';
                image.src = source;
                return image;
            });

            let width = 0;
            let height = 0;
            let imageIndex = 0;
            let lastX = 0;
            let lastY = 0;
            let frame = 0;
            const cursor = {
                x: 0,
                y: 0,
                active: false
            };

            const resize = () => {
                const ratio = window.devicePixelRatio || 1;
                width = canvas.offsetWidth;
                height = canvas.offsetHeight;
                canvas.width = width * ratio;
                canvas.height = height * ratio;
                context.setTransform(ratio, 0, 0, ratio, 0, 0);
            };

            const addNode = (x, y, force = false) => {
                if (!force && Math.hypot(x - lastX, y - lastY) < 54) {
                    return;
                }

                lastX = x;
                lastY = y;

                const angle = Math.random() * Math.PI * 2;
                const speed = 0.18 + Math.random() * 0.42;
                const size = 82 + Math.random() * 54;

                nodes.push({
                    x,
                    y,
                    vx: Math.cos(angle) * speed,
                    vy: Math.sin(angle) * speed,
                    size,
                    life: 1,
                    image: photos[imageIndex % photos.length]
                });

                imageIndex += 1;

                if (nodes.length > 18) {
                    nodes.shift();
                }
            };

            const drawPhoto = (node) => {
                const photoWidth = node.size;
                const photoHeight = node.size * 1.2;
                const x = node.x - photoWidth / 2;
                const y = node.y - photoHeight / 2;
                const radius = 6;

                context.save();
                context.globalAlpha = Math.max(0, node.life);
                context.beginPath();
                context.moveTo(x + radius, y);
                context.lineTo(x + photoWidth - radius, y);
                context.quadraticCurveTo(x + photoWidth, y, x + photoWidth, y + radius);
                context.lineTo(x + photoWidth, y + photoHeight - radius);
                context.quadraticCurveTo(x + photoWidth, y + photoHeight, x + photoWidth - radius, y + photoHeight);
                context.lineTo(x + radius, y + photoHeight);
                context.quadraticCurveTo(x, y + photoHeight, x, y + photoHeight - radius);
                context.lineTo(x, y + radius);
                context.quadraticCurveTo(x, y, x + radius, y);
                context.clip();

                if (node.image.complete && node.image.naturalWidth) {
                    const scale = Math.max(photoWidth / node.image.naturalWidth, photoHeight / node.image.naturalHeight);
                    const sourceWidth = photoWidth / scale;
                    const sourceHeight = photoHeight / scale;
                    const sourceX = (node.image.naturalWidth - sourceWidth) / 2;
                    const sourceY = (node.image.naturalHeight - sourceHeight) / 2;
                    context.drawImage(node.image, sourceX, sourceY, sourceWidth, sourceHeight, x, y, photoWidth, photoHeight);
                } else {
                    context.fillStyle = '#b89a6d';
                    context.fillRect(x, y, photoWidth, photoHeight);
                }

                context.restore();
                context.save();
                context.globalAlpha = Math.max(0, node.life) * 0.9;
                context.strokeStyle = 'rgba(255, 252, 247, 0.9)';
                context.lineWidth = 1;
                context.strokeRect(x, y, photoWidth, photoHeight);
                context.restore();
            };

            const drawNetwork = () => {
                context.clearRect(0, 0, width, height);
                context.fillStyle = '#f6f1e9';
                context.fillRect(0, 0, width, height);

                if (cursor.active) {
                    nodes.forEach((node) => {
                        const distance = Math.hypot(node.x - cursor.x, node.y - cursor.y);

                        if (distance < 360) {
                            context.beginPath();
                            context.moveTo(cursor.x, cursor.y);
                            context.lineTo(node.x, node.y);
                            context.strokeStyle = `rgba(126, 72, 50, ${0.22 + (1 - distance / 360) * node.life * 0.64})`;
                            context.lineWidth = 2.2;
                            context.stroke();
                        }
                    });

                    context.beginPath();
                    context.arc(cursor.x, cursor.y, 5, 0, Math.PI * 2);
                    context.fillStyle = 'rgba(126, 72, 50, 0.9)';
                    context.fill();
                    context.beginPath();
                    context.arc(cursor.x, cursor.y, 12, 0, Math.PI * 2);
                    context.strokeStyle = 'rgba(126, 72, 50, 0.28)';
                    context.lineWidth = 1.4;
                    context.stroke();
                } else {
                    for (let i = 0; i < nodes.length; i += 1) {
                        for (let j = i + 1; j < nodes.length; j += 1) {
                            const a = nodes[i];
                            const b = nodes[j];
                            const distance = Math.hypot(a.x - b.x, a.y - b.y);

                            if (distance < 260) {
                                context.beginPath();
                                context.moveTo(a.x, a.y);
                                context.lineTo(b.x, b.y);
                                context.strokeStyle = `rgba(126, 72, 50, ${0.16 + (1 - distance / 260) * Math.min(a.life, b.life) * 0.6})`;
                                context.lineWidth = 1.8;
                                context.stroke();
                            }
                        }
                    }
                }

                nodes.forEach((node) => {
                    node.x += node.vx;
                    node.y += node.vy;
                    node.vx += Math.sin(frame * 0.012 + node.size) * 0.002;
                    node.vy += Math.cos(frame * 0.01 + node.size) * 0.002;
                    node.life -= 0.0038;
                    drawPhoto(node);
                });

                for (let i = nodes.length - 1; i >= 0; i -= 1) {
                    if (nodes[i].life <= 0) {
                        nodes.splice(i, 1);
                    }
                }

                frame += 1;

                if (!reduceMotion) {
                    requestAnimationFrame(drawNetwork);
                }
            };

            resize();

            if (!canHover || reduceMotion) {
                const seeds = [
                    [0.25, 0.34],
                    [0.42, 0.58],
                    [0.58, 0.36],
                    [0.74, 0.56]
                ];

                seeds.forEach(([x, y]) => addNode(width * x, height * y, true));
            }

            hero.addEventListener('mousemove', (event) => {
                if (!canHover || reduceMotion) {
                    return;
                }

                const rect = hero.getBoundingClientRect();
                const centerX = event.clientX - rect.left;
                const centerY = event.clientY - rect.top;
                cursor.x = centerX;
                cursor.y = centerY;
                cursor.active = true;

                const t = (imageIndex % 24) / 24 * Math.PI * 2;
                const scale = 4.8;
                const heartX = 16 * Math.pow(Math.sin(t), 3);
                const heartY = -(
                    13 * Math.cos(t) -
                    5 * Math.cos(2 * t) -
                    2 * Math.cos(3 * t) -
                    Math.cos(4 * t)
                );

                addNode(centerX + heartX * scale, centerY + heartY * scale);
            });

            hero.addEventListener('mouseleave', () => {
                cursor.active = false;
            });

            drawNetwork();

            window.addEventListener('resize', () => {
                resize();
                nodes.length = 0;
            });
        }
    </script> -->
</body>
</html>
