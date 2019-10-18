import axios from 'axios'
import swal from 'sweetalert2'
import Cookies from 'js-cookie'
import queryString from 'query-string'
import config from '../../config'
require('es6-promise').polyfill()

axios.interceptors.request.use(function (xhr) {
  // Do something before request is sent

    if (!(xhr.data instanceof FormData)) {
        xhr.data = queryString.stringify(xhr.data)
    }

    if (Cookies.get(config.cookieTokenName) !== undefined) {
        xhr.headers['Authorization'] = 'Bearer ' + Cookies.get(config.cookieTokenName)
    }

    return xhr;
}, function (error) {
    // Do something with request error
    return Promise.reject(error)
})

axios.interceptors.response.use(function (res) {
    if (res.headers['authorization-token']) {
        const newToken = res.headers['authorization-token']

        Cookies.set(config.cookieTokenName, newToken, {
            expires: config.cookieExpirationDay
        })
    }

    if (res.data.status === 401) {
        Cookies.remove(config.cookieTokenName, { path: '/' })
            swal({
                title: 'Session Expired',
                text: "Your session is expired. Re-login",
                type: 'info'
            })
        .then(result => {
            window.location.href = `${config.publicBaseUrl}/login`
        })
    } else if (res.data.status === 403) {
        window.location.href = `${config.publicBaseUrl}/dashboard`
    }

    return res

}, function (error) {
    return Promise.reject(error)
})

