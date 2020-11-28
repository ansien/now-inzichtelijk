import { library, dom } from '@fortawesome/fontawesome-svg-core'
import { faSpinner } from '@fortawesome/free-solid-svg-icons'
import { grid } from './grid';

import '../css/app.scss';

require('bootstrap');

library.add(faSpinner);

grid.render(document.getElementById('grid'));

dom.watch();