import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// --- ADD THESE LINES ---
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;
window.axios.defaults.baseURL = 'http://localhost:8000'; // Ensure this matches your backend URL
