// resources/js/bootstrap.js

import _ from 'lodash';
window._ = _;

import 'bootstrap';
import 'admin-lte';

// Example: Axios setup (Laravel default)
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
