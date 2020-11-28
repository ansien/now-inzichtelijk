import $ from 'jquery';
import { firstBatchGrid } from './grid/firstBatchGrid';
import { secondBatchGrid } from './grid/secondBatchGrid';
import { library, dom } from '@fortawesome/fontawesome-svg-core'
import { faSpinner } from '@fortawesome/free-solid-svg-icons'

import '../css/app.scss';

library.add(faSpinner);

require('bootstrap');

let firstBatchGridInitialized = false;
let secondBatchGridInitialized = false;

const checkGrids = () => {
    if (!firstBatchGridInitialized) {
        firstBatchGrid.render(document.getElementById('first-batch-grid'));
        firstBatchGridInitialized = true;
    }
    if (!secondBatchGridInitialized) {
        secondBatchGrid.render(document.getElementById('second-batch-grid'));
        secondBatchGridInitialized = true;
    }
}

$('.nowi-grids .nowi-nav .nowi-nav__item[data-toggle="tab"]').on('shown.bs.tab', () => {
    checkGrids();
})

$(() => checkGrids());
dom.watch();