import { library, dom } from '@fortawesome/fontawesome-svg-core'
import { faSpinner } from '@fortawesome/free-solid-svg-icons'
import { grid } from './grid';
import $ from 'jquery';

import '../css/app.scss';

require('bootstrap');

library.add(faSpinner);

grid.render(document.getElementById('grid'));

dom.watch();

$(document).on('click', '.nowi-modal-toggle', function () {
    const companyId = $(this).data('company-id');
    $('.nowi-modal__body').load(`/company/${companyId}/detail-modal`);
    $('.nowi-modal').modal('show');
})
