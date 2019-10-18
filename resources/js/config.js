const publicBaseUrl = process.env.NODE_ENV === 'development' ? '/grabmaid_laravel/public/admin' : '/admin'
// const publicBaseUrl = process.env.NODE_ENV === 'development' ? '/server/admin' : '/admin'

export const defaultConfig = {
    cookieTokenName: 'gm_auth_token',
    cookieExpirationDay: 1/24,
    userType: {
        1: 'Admin',
        2: 'Vendor',
        3: 'Cleaner',
        4: 'User'
    },
    dayOfWeek: {
        1: 'Monday',
        2: 'Tuesday',
        3: 'Wednesday',
        4: 'Thursday',
        5: 'Friday',
        6: 'Saturday',
        7: 'Sunday',
    }
}

export const paths = {
    settings: {
        base: '/settings',
        users: `/settings/users`,
        addresses: `/settings/user/addresses`,
        zones: `/settings/zones`,
        services: `/settings/services`,
        extracharge: `/settings/extra`,
        promotion: `/settings/promotion`,
        feedback: `/settings/feedback`
    },
    bookings: {
        base: '/bookings',
        list: '/bookings/list',
        create: '/bookings/create',
    },
    vendors: {
        base: '/vendors',
        company: '/vendors/companies',
        cleaner: '/vendors/cleaners',
        services: '/vendors/services',
    },
    ewallet:'/ewallet',
}

const api = {
    admin: {
        login: `${baseAPIUrl}/admin/login`,
    },
    logout: `${baseAPIUrl}/logout`,
    users: {
        base: `${baseAPIUrl}/users`,
        profileUpdate: `${baseAPIUrl}/user/profile-update`,
        bookings: `${baseAPIUrl}/user-bookings`,
        addresses: `${baseAPIUrl}/user/addresses`,
        password: `${baseAPIUrl}/user/password`,
    },
    services: `${baseAPIUrl}/services`,
    ewallet: `${baseAPIUrl}/ewallet`,
    zone: `${baseAPIUrl}/zones`,
    extra: `${baseAPIUrl}/extra`,
    promotion: `${baseAPIUrl}/promotion`,
    feedback: `${baseAPIUrl}/feedback`,
    setting: `${baseAPIUrl}/settings`,
    vendor: {
        company: `${baseAPIUrl}/vendor-companies`,
        cleaner: `${baseAPIUrl}/vendor-cleaner`,
        services: `${baseAPIUrl}/vendor-services`,
        bank: `${baseAPIUrl}/vendor-bank`
    },
    regions: `${baseAPIUrl}/regions`,
    states: `${baseAPIUrl}/states`,
    locations: `${baseAPIUrl}/locations`,
    bookings: {
        base: `${baseAPIUrl}/bookings`,
        status: `${baseAPIUrl}/bookings/status`,
    },
    test: `${baseUrl}/closed`,

}

export default {
    publicBaseUrl,
    api,
    paths,
    ...defaultConfig
}
