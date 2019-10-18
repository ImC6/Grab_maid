import React from 'react'
import axios from 'axios'
import Select from 'react-select'
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap'
import {
    swalSuccess,
    swalErr,
    isEmpty,
    convertTime,
    tryParseJson
} from '../../services/helper/utilities'
import config from '../../config'

const defaultErrMsg = {
    service_id: '',
    price: '',
    start_time: '',
    duration: '',
    regions: '',
    city: '',
    state: '',
    cleaners: '',
}

class VendorCleanerPage extends React.Component {
    constructor(props) {
        super(props)

        this.state = {
            vendor: {},
            serviceList: [],
            isServiceModalOpen: false,
            selectedService: {},
            editableSelectedService: {},
            services: [],
            regions: [],
            states: [],
            cities: [],
            cleaners: [],
            workingDays: {
                1: false,
                2: false,
                3: false,
                4: false,
                5: false,
                6: false,
                7: false,
            },
            isCheckedWeekday: false,
            isCheckedWeekend: false,
            errMsg: {...defaultErrMsg},
            saveServiceAction: '',
        }

        this.getVendorServiceList = this.getVendorServiceList.bind(this)
        this.getServiceList = this.getServiceList.bind(this)
        this.getLocationList = this.getLocationList.bind(this)
        this.getCleanerList = this.getCleanerList.bind(this)
        this.modalEnter = this.modalEnter.bind(this)
        this.clearModalData = this.clearModalData.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.handleChangeSelectSingle = this.handleChangeSelectSingle.bind(this)
        this.handleChangeSelectMultiple = this.handleChangeSelectMultiple.bind(this)
        this.selectService = this.selectService.bind(this)
        this.createService = this.createService.bind(this)
        this.saveService = this.saveService.bind(this)
        this.convertCleanersToOptions = this.convertCleanersToOptions.bind(this)
        this.convertRegionsToOptions = this.convertRegionsToOptions.bind(this)
        this.setWorkingDayBy = this.setWorkingDayBy.bind(this)
    }

    componentDidMount() {
        this.getVendorServiceList()
    }

    getVendorServiceList() {
        axios.get(`${config.api.vendor.services}/${this.props.match.params.companyId}`)
        .then(res => {
            if (res.data.status === 200) {
                this.setState({
                    vendor: res.data.vendor,
                    serviceList: res.data.services
                })
            }
        })
    }

    getServiceList() {
        axios.get(config.api.services)
        .then(res => {
            if (res.data.status === 200) {
                const services = res.data.services.map(service => {
                    return {
                        value: service.id,
                        label: service.name,
                    }
                })
                this.setState({
                    services: services
                })
            }
        })
    }

    getLocationList(params = {}, updateStates = false, updateCity = false) {
        axios.get(config.api.locations, {
            params: params
        })
        .then(res => {
            if (res.data.status === 200) {
                const regionOptions = res.data.regions.map(region => {
                    return {
                        value: region,
                        label: region,
                    }
                })

                this.setState({
                    regions: regionOptions
                })
                if (updateStates) {
                    this.setState({
                        states: res.data.states
                    })
                }
                if (updateCity) {
                    this.setState({
                        cities: res.data.cities
                    })
                }
            }
        })
    }

    getCleanerList() {
        axios.get(`${config.api.vendor.cleaner}/${this.state.vendor.guid}`)
        .then(res => {
            if (res.data.status === 200) {
                const cleanerData = res.data.cleaners.map(cleaner => {
                    return {
                        value: cleaner.guid,
                        label: cleaner.name,
                    }
                })

                this.setState({
                    cleaners: cleanerData
                })
            }
        })
    }

    handleChange(e) {
        const property = e.target.id
        const value = e.target.value
        this.setState(prevState => {
            return {
                editableSelectedService: {
                    ...prevState.editableSelectedService,
                    [property]: value
                }
            }
        })

        if (property === 'state') {
            this.getLocationList({
                [property]: value
            }, false, true);
        }

        if (property === 'city') {
            this.getLocationList({
                state: this.state.editableSelectedService.state,
                [property]: value
            });
        }
    }

    handleChangeSelectSingle(selectedOption, el) {
        this.setState(prevState => {
            return {
                editableSelectedService: {
                    ...prevState.editableSelectedService,
                    [el.name]: selectedOption.value
                }
            }
        })
    }

    handleChangeSelectMultiple(selectedOption, el) {
        this.setState(prevState => {
            return {
                editableSelectedService: {
                    ...prevState.editableSelectedService,
                    [el.name]: selectedOption
                }
            }
        })
    }

    handleDayChange(day) {
        this.setState(prevState => {
            return {
                workingDays: {
                    ...prevState.workingDays,
                    [day]: !prevState.workingDays[day]
                }
            }
        })
    }

    setWorkingDayBy(e) {
        const type = e.target.id
        let property, newWorkingDays

        switch (type) {
            case 'weekday':
                property = 'isCheckedWeekday'
                if (this.state[property]) {
                    newWorkingDays = {
                        ...this.state.workingDays,
                        1: false,
                        2: false,
                        3: false,
                        4: false,
                        5: false,
                    }
                } else {
                    newWorkingDays = {
                        ...this.state.workingDays,
                        1: true,
                        2: true,
                        3: true,
                        4: true,
                        5: true,
                    }
                }
            break;
                case 'weekend':
                property = 'isCheckedWeekend'
                if (this.state[property]) {
                    newWorkingDays = {
                        ...this.state.workingDays,
                        6: false,
                        7: false,
                    }
                } else {
                    newWorkingDays = {
                        ...this.state.workingDays,
                        6: true,
                        7: true,
                    }
                }
                break;
            default:
                console.error('Undefined check day type!')
                return false
        }

        this.setState(prevState => ({
            [property]: !prevState[property],
            workingDays: newWorkingDays
        }))
    }

    selectService(e, service) {
        e.preventDefault()
        this.modalEnter(service)

        const workingDay = tryParseJson(service.working_day)
        let newWorkingDay = {}
        workingDay.forEach(day => {
            newWorkingDay[day] = true
        })

        this.setState(prevState => {
            return {
                saveServiceAction: 'update',
                selectedService: {
                    ...service,
                    regions: this.convertRegionsToOptions(service.regions),
                    cleaners: this.convertCleanersToOptions(service.cleaners)
                },
                editableSelectedService: {
                    ...service,
                    regions: this.convertRegionsToOptions(service.regions),
                    cleaners: this.convertCleanersToOptions(service.cleaners)
                },
                isServiceModalOpen: true,
                workingDays: {
                    ...prevState.workingDays,
                    ...newWorkingDay
                }
            }
        })

        // this.getLocationList({
        //     state: service.state,
        //     city: service.city
        // }, false, true);
    }

    createService() {
        this.modalEnter()
        this.setState({
            isServiceModalOpen: true,
            saveServiceAction: 'create',
            editableSelectedService: {
                service_id: '',
                start_time: '',
                duration: 2,
                regions: [],
                price: 0.00,
                cleaners: [],
                start_date: '',
                end_date: '',
            }
        })
    }

    saveService() {
        const regions = this.state.editableSelectedService.regions.map(region => region.value)
        const cleaners = this.state.editableSelectedService.cleaners.map(cleaner => cleaner.value)
        const workingDays = Object.keys(this.state.workingDays).filter(day => {
            return this.state.workingDays[day] === true
        }).map(day => {
            return (typeof day !== 'number') ? parseInt(day, 10) : day
        });
        const serviceIds = this.state.editableSelectedService.service_id.map(serviceId => serviceId.value);

        const params = {
            service_id: serviceIds[0],
            start_time: this.state.editableSelectedService.start_time,
            duration: this.state.editableSelectedService.duration,
            regions: JSON.stringify(regions),
            state: this.state.editableSelectedService.state,
            city: this.state.editableSelectedService.city,
            price: this.state.editableSelectedService.price,
            cleaners: JSON.stringify(cleaners),
            start_date: this.state.editableSelectedService.start_date,
            end_date: this.state.editableSelectedService.end_date,
            working_day: JSON.stringify(workingDays),
        }

        if (this.state.saveServiceAction === 'create') {
            axios.post(`${config.api.vendor.services}/${this.props.match.params.companyId}`, params)
            .then(res => {
                if (res.data.status === 200) {
                    swalSuccess({
                        text: res.data.message
                    }).then(() => {
                        this.setState({
                            isServiceModalOpen: false,
                            selectedService: {},
                            editableSelectedService: {},
                            saveServiceAction: '',
                        })
                        this.getVendorServiceList()
                    })
                } else if (res.data.status === 400) {
                    const errors = res.data.errors
                    const message = Object.keys(errors).map(field => {
                        return errors[field][0];
                    })
                    swalErr({
                        text: '',
                        html: message.join('<br />')
                    })
                } else {
                    swalErr().then(() => {
                        window.location.reload()
                    })
                }
            })
        } else if (this.state.saveServiceAction === 'update') {
            axios.put(`${config.api.vendor.services}/${this.state.editableSelectedService.vendor_service_id}`, params)
            .then(res => {
                if (res.data.status === 200) {
                    swalSuccess({
                        text: res.data.message
                    }).then(() => {
                        this.setState({
                            isServiceModalOpen: false,
                            selectedService: {},
                            editableSelectedService: {},
                            saveServiceAction: '',
                        })
                        this.getVendorServiceList()
                    })
                } else if (res.data.status === 400) {
                    const errors = res.data.errors
                    const message = Object.keys(errors).map(field => {
                        return errors[field][0];
                    })
                    swalErr({
                        text: '',
                        html: message.join('<br />')
                    })
                } else {
                    swalErr().then(() => {
                        window.location.reload()
                    })
                }
            })
        }
    }

    modalEnter(service = null) {
        if (this.state.services.length === 0) {
            this.getServiceList()
        }
        if (this.state.regions.length === 0) {
            let getRegionParams = {};
            if (service) {
                getRegionParams.state = service.state;
                getRegionParams.city = service.city;
            }
            this.getLocationList(getRegionParams, true, true)
        }
        if (this.state.cleaners.length === 0) {
            this.getCleanerList()
        }
    }

    toggleModal(modal) {
        this.setState(prevState => {
            return {
                [modal]: !prevState[modal]
            }
        })
    }

    clearModalData() {
        this.setState({
            selectedService: {},
            editableSelectedService: {},
            workingDays: {
                1: false,
                2: false,
                3: false,
                4: false,
                5: false,
                6: false,
                7: false,
            },
            isCheckedWeekday: false,
            isCheckedWeekend: false,
        })
    }

    convertCleanersToOptions(cleaners) {
        const cleanerOptions = cleaners.map(cleaner => {
            return {
                label: cleaner.name,
                value: cleaner.guid
            }
        })

        return cleanerOptions
    }

    convertRegionsToOptions(regions) {
        const regionOptions = regions.map(region => {
            return {
                label: region,
                value: region
            }
        })

        return regionOptions
    }

    render() {
        const serviceList = this.state.serviceList.map((service, index) => {
            return (
                <tr key={index}>
                    <td>{index + 1}</td>
                    <td>
                        <a href="#" onClick={(e) => this.selectService(e, service)}>{service.name}</a>
                    </td>
                    <td>{ convertTime(service.start_time) }</td>
                    <td>{service.duration}</td>
                    <td>
                        {service.regions.join(', ')}
                    </td>
                    <td>
                        {service.cleaners.length}
                        {/* {
                            service.cleaners.map((cleaner, id) => {
                                return (
                                    <span key={id} className="mr-2">{cleaner.name || ''}</span>
                                )
                            })
                        } */}
                    </td>
                    <td>{service.price}</td>
                    <td>{service.start_date} - {service.end_date}</td>
                </tr>
            )
        })

        const workingDayList = Object.keys(this.state.workingDays).map((day, index) => {
            return (
                <div key={index} className="form-check form-check-inline">
                    <input onChange={e => this.handleDayChange(day)} className="form-check-input" type="checkbox" checked={this.state.workingDays[day]} id={`working_day_${day}`} />
                    <label className="form-check-label" htmlFor={`working_day_${day}`}>{config.dayOfWeek[day]}</label>
                </div>
            )
        })

        return (
            <div id="vendor-management">
                <h3>
                    <a href="#" className="btn btn-link" onClick={this.props.history.goBack}>
                        <i className="fas fa-chevron-left"></i>
                    </a>
                    <span className="ml-2">Vendor Services</span>
                    <button onClick={this.createService} className="btn btn-success btn-sm ml-2">Add services</button>
                </h3>

                <div className="table-responsive">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Service</th>
                                <th>Start Time</th>
                                <th>Hours</th>
                                <th>Area</th>
                                <th>Cleaners</th>
                                <th>Price</th>
                                <th>Period</th>
                            </tr>
                        </thead>
                        <tbody>
                            {serviceList.length > 0 ? serviceList : (
                                <tr>
                                    <td colSpan="5">List is empty!</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Modal size="lg" isOpen={this.state.isServiceModalOpen} toggle={() => this.toggleModal('isServiceModalOpen')} onClosed={this.clearModalData}>
                    <ModalHeader toggle={() => this.toggleModal('isServiceModalOpen')}>Service</ModalHeader>
                    {
                        !isEmpty(this.state.editableSelectedService)
                        ? (
                            <ModalBody>
                                <div className="row">
                                    <div className="col-sm-6">
                                        <div className="form-group">
                                            <label>Service</label>
                                            <Select
                                                defaultValue={isEmpty(this.state.selectedService) ? [] : {
                                                    value: this.state.selectedService.service_id,
                                                    label: this.state.selectedService.name
                                                }}
                                                isMulti
                                                name="service_id"
                                                options={this.state.services}
                                                className="select"
                                                onChange={this.handleChangeSelectMultiple}
                                            />
                                        </div>
                                    </div>
                                    <div className="col-sm-6">
                                        <div className="form-group">
                                            <label>Price</label>
                                            <input id="price" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedService.price} />
                                        </div>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-sm-6">
                                        <div className="form-group">
                                            <label>Start Time</label>
                                            <input id="start_time" className={`form-control`} onChange={this.handleChange} placeholder="eg, 08:00" value={this.state.editableSelectedService.start_time} />
                                        </div>
                                    </div>
                                    <div className="col-sm-6">
                                        <div className="form-group">
                                            <label>Duration</label>
                                            <select id="duration" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedService.duration}>
                                                <option value="2">2</option>
                                                <option value="4">4</option>
                                                <option value="6">6</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-sm-6">
                                        <div className="form-group">
                                            <label>State</label>
                                            <select id="state" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedService.state}>
                                                <option value="">Please Select...</option>
                                                {
                                                    this.state.states.map((state, id) => {
                                                        return (
                                                            <option key={`${state}_${id}`} value={state}>{state}</option>
                                                        )
                                                    })
                                                }
                                            </select>
                                        </div>
                                    </div>
                                    <div className="col-sm-6">
                                        <div className="form-group">
                                            <label>City</label>
                                            <select id="city" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedService.city}>
                                                <option value="">Please Select...</option>
                                                {
                                                    this.state.cities.map((city, id) => {
                                                        return (
                                                            <option key={`${city}_${id}`} value={city}>{city}</option>
                                                        )
                                                    })
                                                }
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-sm-6">
                                        <div className="form-group">
                                            <label>Regions</label>
                                            <Select
                                                defaultValue={isEmpty(this.state.editableSelectedService) ? [] : this.state.editableSelectedService.regions}
                                                isMulti
                                                name="regions"
                                                options={this.state.regions}
                                                className="basic-multi-select"
                                                classNamePrefix="select"
                                                onChange={this.handleChangeSelectMultiple}
                                            />
                                        </div>
                                    </div>

                                    <div className="col-sm-6">
                                        <div className="form-group">
                                            <label>Cleaners</label>
                                            <Select
                                                defaultValue={isEmpty(this.state.editableSelectedService) ? [] : this.state.editableSelectedService.cleaners}
                                                isMulti
                                                name="cleaners"
                                                options={this.state.cleaners}
                                                className="basic-multi-select"
                                                classNamePrefix="select"
                                                onChange={this.handleChangeSelectMultiple}
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-sm-6">
                                        <div className="form-group">
                                            <label>Start Date</label>
                                            <input id="start_date" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedService.start_date || ''} />
                                        </div>
                                    </div>
                                    <div className="col-sm-6">
                                        <div className="form-group">
                                            <label>End Date</label>
                                            <input id="end_date" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedService.end_date || ''} />
                                        </div>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-sm-12">
                                        <div className="form-group">
                                            <label className="d-block">Working Day</label>
                                            {workingDayList}
                                            <hr />
                                            <div className="form-check form-check-inline">
                                                <input onChange={this.setWorkingDayBy} className="form-check-input" type="checkbox" checked={this.state.isCheckedWeekday} id="weekday" />
                                                <label className="form-check-label" htmlFor="weekday">Weekday</label>
                                            </div>
                                            <div className="form-check form-check-inline">
                                                <input onChange={this.setWorkingDayBy} className="form-check-input" type="checkbox" checked={this.state.isCheckedWeekend} id="weekend" />
                                                <label className="form-check-label" htmlFor="weekend">Weekend</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </ModalBody>
                        )
                        : null
                    }
                    <ModalFooter>
                        <button className="btn btn-primary" onClick={this.saveService}>Save</button>
                        <button className="btn btn-secondary" onClick={() => this.toggleModal('isServiceModalOpen')}>Cancel</button>
                    </ModalFooter>
                </Modal>
            </div>
        )
    }
}

export default VendorCleanerPage
