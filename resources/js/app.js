import '@fontsource/baloo-2/latin-600.css';
import '@fontsource/baloo-2/latin-800.css';
import '@fontsource/lexend/latin-400.css';
import '@fontsource/lexend/latin-500.css';

import Alpine from 'alpinejs';
import codeCanvas from './components/codeCanvas.js';
import { worldMap, resume, badgeShelf } from './components/worldMap.js';

window.Alpine = Alpine;
Alpine.data('codeCanvas', codeCanvas);
Alpine.data('worldMap', worldMap);
Alpine.data('resume', resume);
Alpine.data('badgeShelf', badgeShelf);
Alpine.start();
