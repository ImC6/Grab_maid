import React from 'react'
import axios from 'axios'
import Select from 'react-select'
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap'
import {
    swalSuccess,
    swalErr
} from '../../services/helper/utilities'
import config from '../../config'

class BookingCreatePage extends React.Component {
    constructor(props) {
        super(props)

        this.state = {
            userList: [],
            regionList: [],
            vendorServiceList:[],
            serviceList: [],
            user_guid: '',
            service_id: '',
            vendor_service_id: '',
            region: '',
            isCreateModalOpen: false,
            errMsg: {}
        }

        this.getUserList = this.getUserList.bind(this)
        this.getRegionList = this.getRegionList.bind(this)
        this.getServiceList = this.getServiceList.bind(this)
        this.getVendorServiceList = this.getVendorServiceList.bind(this)
        this.getServiceSessions = this.getServiceSessions.bind(this)
        this.selectBooking = this.selectBooking.bind(this)
        this.saveBooking = this.saveBooking.bind(this)
        this.toggleModal = this.toggleModal.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.handleChangeSelect = this.handleChangeSelect.bind(this)
    }

    componentWillMount() {
        this.getRegionList()
        this.getServiceList()
    }

    getUserList() {
        axios.get(config.api.users.base)
        .then(res => {
            if (res.data.status === 200) {
                const users = res.data.users.map(user => {
                    return {
                        value: user.guid,
                        label: user.email,
                    }
                })

                this.setState({
                    userList: users
                })
            }
        })
    }

    getRegionList() {
        axios.get(config.api.regions)
        .then(res => {
            if (res.data.status === 200) {
                const regions = res.data.regions.map(region => {
                    return {
                        value: region,
                        label: region,
                    }
                })

                this.setState({
                    regionList: regions
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
                    serviceList: services
                })
            }
        })
    }

    getVendorServiceList() {
        axios.get(config.api.vendor.services, {
            params: {
                region: this.state.region,
                service_id: this.state.service_id,
            }
        })
        .then(res => {
            if (res.data.status === 200) {
                this.setState({
                    vendorServiceList: res.data.services
                })
            }
        })
    }

    getServiceSessions() {
        if (this.state.region === '') {
            swalErr({
                text: 'Please select region!'
            }).then(() => {
                setTimeout(() => {
                    this.regionSelectInput.focus()
                }, 300)
            })
            return false
        }

        if (this.state.service_id === '') {
            swalErr({
                text: 'Please select service!'
            }).then(() => {
                setTimeout(() => {
                    this.serviceSelectInput.focus()
                }, 300)
            })
            return false
        }

        this.getVendorServiceList()
    }

    handleChange(e) {
        const property = e.target.id
        const value = e.target.value
        // this.setState(prevState => {
        //     return {
        //         editableSelectedCleaner: {
        //             ...prevState.editableSelectedCleaner,
        //             [property]: value
        //         }
        //     }
        // })
    }

    handleChangeSelect(selectedOption, e) {
        this.setState({
            [e.name]: selectedOption.value
        })
    }

    toggleModal(modal) {
        this.setState(prevState => {
            return {
                [modal]: !prevState[modal]
            }
        })
    }

    selectBooking(e, vendorServiceId) {
        e.preventDefault()

        this.getUserList()
        this.setState({
            isCreateModalOpen: true,
            vendor_service_id: vendorServiceId
        })
    }

    saveBooking() {
        if (this.state.user_guid === '') {
            swalErr({
                text: 'Please select user!'
            }).then(() => {
                setTimeout(() => {
                    this.userSelectInput.focus()
                }, 300)
            })
            return false
        }

        axios.post(`${config.api.users.bookings}/${this.state.user_guid}`, {
            vendor_service_id: this.state.vendor_service_id
        })
        .then(res => {
            if (res.data.status === 200) {
                swalSuccess({
                    text: res.data.message
                }).then(() => {
                    this.props.history.push(config.paths.bookings.list)
                })
            }
        })
    }

    render() {
        const vendorServiceList = this.state.vendorServiceList.map((service, index) => {
            return (
                <tr key={index}>
                    <td>{index + 1}</td>
                    <td>{service.service.name}</td>
                    <td>{service.vendor.name}</td>
                    <td>{service.start_time}</td>
                    <td>{service.duration}</td>
                    <td>{service.price}</td>
                    <td>
                        <button className="btn btn-success btn-sm" onClick={(e) => this.selectBooking(e, service.id)}>Add</button>
                    </td>
                </tr>
            )
        })

        return (
            <div id="booking-list">
                <h3>
                    <span className="ml-2">Create Booking</span>
                </h3>

                <div className="container">
                    <div className="row">
                        <div className="col-3">
                            <div className="form-group">
                                <label>Region</label>
                                <Select
                                    ref={el => this.regionSelectInput = el}
                                    defaultValue={[]}
                                    name="region"
                                    options={this.state.regionList}
                                    className="select"
                                    onChange={this.handleChangeSelect}
                                />
                            </div>
                        </div>
                        <div className="col-3">
                            <div className="form-group">
                                <label>Service</label>
                                <Select
                                    ref={el => this.serviceSelectInput = el}
                                    defaultValue={[]}
                                    name="service_id"
                                    options={this.state.serviceList}
                                    className="select"
                                    onChange={this.handleChangeSelect}
                                />
                            </div>
                        </div>
                        {/* <div className="col-3">
                            <div className="form-group">
                                <label>User</label>
                                <Select
                                    ref={el => this.userSelectInput = el}
                                    defaultValue={[]}
                                    name="user_guid"
                                    options={this.state.userList}
                                    className={`select is-invalid`}
                                    onChange={this.handleChangeSelect}
                                />
                            </div>
                        </div> */}
                        <div className="col-3">
                            <div className="form-group">
                                <button onClick={this.getServiceSessions} className="btn btn-success btn-sm">Search</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="table-responsive">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Service</th>
                                <th>Vendor</th>
                                <th>Start Time</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>
                                    <i className="fas fa-cog"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {vendorServiceList.length > 0 ? vendorServiceList : (
                                <tr>
                                    <td colSpan="5">List is empty!</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>


                <Modal isOpen={this.state.isCreateModalOpen} toggle={(e) => this.toggleModal('isCreateModalOpen')} onClosed={this.clearModalData}>
                    <ModalHeader toggle={(e) => this.toggleModal('isCreateModalOpen')}>Select User</ModalHeader>
                    <ModalBody>
                        <div className="form-group">
                            <label>User</label>
                            <Select
                                ref={el => this.userSelectInput = el}
                                defaultValue={[]}
                                name="user_guid"
                                options={this.state.userList}
                                className={`select is-invalid`}
                                onChange={this.handleChangeSelect}
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <button onClick={this.saveBooking} className="btn btn-primary">Add</button>
                        <button className="btn btn-secondary" onClick={(e) => this.toggleModal('isCreateModalOpen')}>Cancel</button>
                    </ModalFooter>
                </Modal>
            </div>
        )
    }
}

export default BookingCreatePage
