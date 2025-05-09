<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive 3D Hotelia Experience</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/OBJLoader.js"></script>
    <style>
        body {
            margin: 0;
            overflow-x: hidden;
            height: 400vh;
            background: linear-gradient(to bottom, #0a1f3a, #ffffff);
            font-family: 'Arial', sans-serif;
        }
        
        #canvas-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
        }
        
        .loading-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 24px;
            text-align: center;
            z-index: 1;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }
        
        .scroll-instruction {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            color: white;
            font-size: 18px;
            z-index: 1;
            opacity: 0.9;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
            transition: opacity 0.5s ease;
        }
        
        .scroll-text {
            position: fixed;
            width: 80%;
            max-width: 600px;
            text-align: center;
            color: white;
            font-size: 24px;
            opacity: 0;
            transition: all 0.8s cubic-bezier(0.25, 0.1, 0.25, 1);
            z-index: 2;
            text-shadow: 1px 1px 5px rgba(0,0,0,0.7);
            pointer-events: none;
        }
        
        /* Text positions and entrance directions */
        .text-1 { 
            top: 15%;
            left: 150%;
            transform: translateX(-50%);
        }
        .text-2 { 
            top: 30%;
            right: 150%;
            transform: translateX(50%);
        }
        .text-3 { 
            top: 50%;
            left: 150%;
            transform: translateX(-50%);
        }
        .text-4 { 
            top: 70%;
            right: 150%;
            transform: translateX(50%);
        }
        
        /* Visible state */
        .text-visible {
            opacity: 1;
            left: 50%;
            right: auto;
            transform: translateX(-50%);
        }
        .text-visible.text-2,
        .text-visible.text-4 {
            right: 50%;
            left: auto;
            transform: translateX(50%);
        }
        
        /* Text styling for light background */
        .dark-text {
            color: #333;
            text-shadow: 1px 1px 5px rgba(255,255,255,0.7);
        }
        
        @media (max-width: 768px) {
            .scroll-text {
                font-size: 18px;
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div id="canvas-container"></div>
    <div class="loading-message">Loading 3D Experience...</div>
    <div class="scroll-instruction">Scroll to explore</div>
    
    <!-- Text elements that slide in at different scroll points -->
    <div class="scroll-text text-1">JEEZ ASS CHRIST</div>
    <div class="scroll-text text-2">mfkn text is coming from everywhere</div>
    <div class="scroll-text text-3">look at this slideee</div>
    <div class="scroll-text text-4">The future of smart hotels is here</div>

    <script>
        // Initialize Three.js scene
        const container = document.getElementById('canvas-container');
        let scene, camera, renderer, object;
        let scrollProgress = 0;
        
        // Scene setup with transparent background
        scene = new THREE.Scene();
        scene.background = null;
        
        // Camera setup
        camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.z = 20;
        
        // Renderer setup
        renderer = new THREE.WebGLRenderer({ 
            antialias: true,
            alpha: true
        });
        renderer.setSize(window.innerWidth, window.innerHeight);
        container.appendChild(renderer.domElement);
        
        // Lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
        scene.add(ambientLight);
        
        const directionalLight1 = new THREE.DirectionalLight(0xffffff, 0.9);
        directionalLight1.position.set(0.5, 1, 0.5);
        scene.add(directionalLight1);
        
        // Load 3D object
        const loader = new THREE.OBJLoader();
        loader.load(
            'hotelia.obj',
            function (obj) {
                object = obj;
                scene.add(object);
                
                // Center and scale
                const box = new THREE.Box3().setFromObject(object);
                const center = box.getCenter(new THREE.Vector3());
                object.position.x = -center.x;
                object.position.y = -center.y;
                object.position.z = -center.z;
                object.scale.set(0.3, 0.3, 0.3);
                
                // Apply material
                object.traverse(function(child) {
                    if (child.isMesh) {
                        child.material = new THREE.MeshPhongMaterial({
                            color: 0xffffff,
                            specular: 0x555555,
                            shininess: 100
                        });
                    }
                });
                
                document.querySelector('.loading-message').style.display = 'none';
            },
            function (xhr) {
                console.log((xhr.loaded / xhr.total * 100) + '% loaded');
            },
            function (error) {
                console.error('Error loading model:', error);
                document.querySelector('.loading-message').textContent = 'Error loading model';
            }
        );
        
        // Scroll handler
        window.addEventListener('scroll', () => {
            scrollProgress = Math.min(window.scrollY / (document.body.scrollHeight - window.innerHeight), 1);
            updateModelTransform();
            updateBackground();
            handleTextAppear();
        });
        
        function updateModelTransform() {
            if (object) {
                const scale = 0.3 + (scrollProgress * 0.9);
                object.scale.set(scale, scale, scale);
                object.position.x = THREE.MathUtils.lerp(0, 3, scrollProgress);
                object.position.y = THREE.MathUtils.lerp(0, -2, scrollProgress);
                object.rotation.y = THREE.MathUtils.lerp(0, Math.PI/3, scrollProgress);
                camera.position.z = THREE.MathUtils.lerp(20, 12, scrollProgress);
                camera.lookAt(object.position);
            }
        }
        
        function updateBackground() {
            const startColor = [10, 31, 58];
            const endColor = [255, 255, 255];
            const currentColor = startColor.map((channel, i) => {
                return Math.round(channel + (endColor[i] - channel) * scrollProgress);
            });
            document.body.style.background = `linear-gradient(to bottom, 
                rgb(${startColor.join(',')}), 
                rgb(${currentColor.join(',')}))`;
        }
        
        function handleTextAppear() {
            const scrollPosition = window.scrollY;
            const windowHeight = window.innerHeight;
            const docHeight = document.body.scrollHeight;
            
            // Define scroll thresholds for each text (0-1)
            const textTriggers = [
                { start: 0.1, end: 0.25 },   // Text 1 (slides from right)
                { start: 0.3, end: 0.45 },    // Text 2 (slides from left)
                { start: 0.5, end: 0.65 },    // Text 3 (slides from right)
                { start: 0.7, end: 0.85 }     // Text 4 (slides from left)
            ];
            
            // Handle each text element
            document.querySelectorAll('.scroll-text').forEach((text, index) => {
                const trigger = textTriggers[index];
                const normalizedScroll = scrollPosition / (docHeight - windowHeight);
                
                if (normalizedScroll >= trigger.start && normalizedScroll <= trigger.end) {
                    text.classList.add('text-visible');
                } else {
                    text.classList.remove('text-visible');
                }
                
                // Adjust text color based on background brightness
                if (normalizedScroll > 0.7) {
                    text.classList.add('dark-text');
                } else {
                    text.classList.remove('dark-text');
                }
            });
            
            // Hide initial instruction after first scroll
            if (scrollPosition > 50) {
                document.querySelector('.scroll-instruction').style.opacity = '0';
            } else {
                document.querySelector('.scroll-instruction').style.opacity = '0.9';
            }
        }
        
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
        
        // Initialize
        updateBackground();
        handleTextAppear();
        
        // Animation loop
        function animate() {
            requestAnimationFrame(animate);
            renderer.render(scene, camera);
        }
        
        animate();
    </script>
</body>
</html>