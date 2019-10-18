import Cookies from 'js-cookie'
import config from '../../config'

export default {
  check() {
    if (Cookies.get(config.cookieTokenName) === undefined) return false
    return true
  }
}
