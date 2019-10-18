import moment from 'moment'
import swal from 'sweetalert2'

export const capitalize = (str) => {
  if (typeof str === 'string') {
    return str.charAt(0).toUpperCase() + str.slice(1)
  }
  return ''
}

export const isEmpty = (arg) => {
  if (isObject(arg)) {
    return Object.keys(arg).length === 0 && arg.constructor === Object
  }
  if (typeof arg !== 'number') {
    return arg.length === 0
  }
}

export const isObject = (obj) => {
  return obj !== null && typeof obj === 'object'
}

export const convertStatus = (status) => {
  if (status.toString() === '1') return 'Published'
  if (status.toString() === '0') return 'Draft'
}

export const convertTime = (time) => {
    time = time.toString().match(/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];

    if (time.length > 1) {
        time = time.slice(1);
        time[5] = +time[0] < 12 ? ' AM' : ' PM';
        time[0] = +time[0] % 12 || 12;
    }
    return time.join('');
}

export const tryParseJson = (json, type = 'array') => {
  const ret = () => {
    if (type === 'object') {
      return {}
    }
    if (type === 'array') {
      return []
    }
  }

  try {
    if (json === null) {
      return ret()
    }

    return JSON.parse(json)
  } catch (e) {
    return ret()
  }
}

export const formatDate = (date = moment()) => {
  return moment(date).format('YYYY-MM-DD HH:mm a')
}

export const swalSuccess = (config = {}) => {
  return swal({
    title: 'Success',
    text: "Successfully done",
    type: 'success',
    ...config
  })
}

export const swalErr = (config = {}) => {
  return swal({
    title: 'Error',
    text: "Error occurs",
    type: 'error',
    ...config
  })
}

export const swalDel = (config = {}) => {
  return swal({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#f00',
    confirmButtonText: 'Delete',
    ...config
  })
}
