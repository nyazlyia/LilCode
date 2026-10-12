import '@fontsource/baloo-2/latin-600.css';
import '@fontsource/baloo-2/latin-800.css';
import '@fontsource/lexend/latin-400.css';
import '@fontsource/lexend/latin-500.css';

import Alpine from 'alpinejs';
import codeCanvas from './components/codeCanvas.js';

window.Alpine = Alpine;
Alpine.data('codeCanvas', codeCanvas);
Alpine.start();
