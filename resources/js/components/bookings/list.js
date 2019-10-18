import React from 'react'
import axios from 'axios'
import { Link } from 'react-router-dom'
import Select from 'react-select'
import config from '../../config'
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap'
import {
    isEmpty, swalSuccess, swalErr
} from '../../services/helper/utilities'

const paymentType = {
    1: 'Credit Card',
    2: 'Online Banking',
    3: 'Wallet Coin',
}

const bookingStatus = {
    '-1': 'Cancelled',
    '0': 'Unpaid',
    '1': 'Paid',
    '2': 'Delivering',
    '3': 'In Progress',
    '4': 'Done',
}

class BookingListPage extends React.Component {
    constructor(props) {
        super(props)

        this.state = {
            bookingList: [],
            userOptions: [],
            statusOptions: [],
            isDetailModalOpen: false,
            selectedBooking: {},
            bStatus: '-1',
        }

        this.getBookingList = this.getBookingList.bind(this)
        this.getBookingListByUserId = this.getBookingListByUserId.bind(this)
        this.getUserList = this.getUserList.bind(this)
        this.createBooking = this.createBooking.bind(this)
        this.updateStatus = this.updateStatus.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.changeStatus = this.changeStatus.bind(this)
        this.toggleModal = this.toggleModal.bind(this)
        this.showBookingDetail = this.showBookingDetail.bind(this)
        this.clearModalData = this.clearModalData.bind(this)
        this.do_this = this.do_this.bind(this)
    }

    componentWillMount() {
        this.getBookingList()
        this.getUserList()
    }

    getBookingList() {
        axios.get(config.api.bookings.base)
        .then(res => {
            if (res.data.status === 200) {
                this.setState({
                    bookingList: res.data.bookings
                })
            }
        })
    }

    getBookingListByUserId(guid) {
        axios.get(`${config.api.users.bookings}/${guid}`)
        .then(res => {
            if (res.data.status === 200) {
                this.setState({
                    bookingList: res.data.bookings
                })
            }
        })
    }

    getUserList() {
        axios.get(config.api.users.base)
        .then(res => {
            if (res.data.status === 200) {
                const userOptions = res.data.users.map(user => {
                    return {
                        label: user.email,
                        value: user.guid
                    }
                })
                this.setState({
                    userOptions: userOptions
                })
            }
        })
    }

    handleChange(option) {
        if (option !== null) {
            const guid = option.value
            this.getBookingListByUserId(guid)
        } else {
            this.getBookingList()
        }
    }

    changeStatus(option) {
        this.setState({
            bStatus: option.value
        });
    }

    createBooking() {

    }

    updateStatus(e) {
        const params = {
            status: this.state.bStatus
        };

        axios.put(`${config.api.bookings.status}/${this.state.selectedBooking.id}`, params).then(res => {
            if (res.data.status === 200) {
                swalSuccess({
                    text: 'Status is updated'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                swalErr({
                    text: res.data.message
                });
            }
        });
    }

    showBookingDetail(e, booking) {
        e.preventDefault()
        const statusOptions = Object.keys(bookingStatus).map(status => {
            return {
                label: bookingStatus[status],
                value: status
            }
        })

        this.setState({
            selectedBooking: booking,
            bStatus: booking.status,
            statusOptions: statusOptions,
            isDetailModalOpen: true
        });
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
            selectedBooking: {},
            bStatus: '-1',
            statusOptions: [],
        })
    }

    do_this(){
        var checkboxes = document.getElementsByName('approve[]');
        var button = document.getElementById('toggle');

        if(button.value == 'select'){
            for (var i in checkboxes){
                checkboxes[i].checked = 'FALSE';
            }
            button.value = 'deselect'
        }else{
            for (var i in checkboxes){
                checkboxes[i].checked = '';
            }
            button.value = 'select';
        }
    }

    render() {
        // const statusList = Object.keys(bookingStatus).map((status, id) => {
        //     return (
        //         <option key={id} value={status}>{bookingStatus[status]}</option>
        //     );
        // });

        const list = this.state.bookingList.map((booking, index) => {
            return (
                <tr key={index}>
                    <td><input type='checkbox' name='approve[]' value={booking.id}/></td>
                    <td>{index + 1}</td>
                    <td>
                        <a href="#" onClick={e => this.showBookingDetail(e, booking)}>{booking.booking_number || ''}</a>
                    </td>
                    <td>{booking.user.email || ''}</td>
                    <td>{booking.vendor_service.company.name || ''}</td>
                    <td>{booking.vendor_service.service.name || ''}</td>
                    <td>{booking.booking_date || ''}</td>
                </tr>
            )
        })

        return (
            <div id="booking-list" className="container">
                <h3>
                    Booking List

                    {/* <Link to={config.paths.bookings.create}>
                        <button className="btn btn-success btn-sm ml-2">Add Booking</button>
                    </Link> */}
                </h3>

                <div className="row">
                    <div className="col-sm-4">
                        <div className="form-group">
                            <Select
                                className="basic-single"
                                classNamePrefix="select"
                                isClearable={true}
                                isSearchable={true}
                                options={this.state.userOptions}
                                onChange={this.handleChange}
                            />
                        </div>
                    </div>
                </div>

                <div className="row">
                    <div className="table-responsive">
                        <table className="table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="toggle" value="select" onClick={this.do_this} /></th>
                                    <th>#</th>
                                    <th>Booking ID</th>
                                    <th>User</th>
                                    <th>Vendor</th>
                                    <th>Service</th>
                                    <th>Booking Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                {list.length > 0 ? list : (
                                    <tr>
                                        <td colSpan="5">No Bookings</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                <Modal size="lg" isOpen={this.state.isDetailModalOpen} toggle={(e) => this.toggleModal('isDetailModalOpen')} onClosed={this.clearModalData}>
                    <ModalHeader toggle={(e) => this.toggleModal('isDetailModalOpen')}>Booking {isEmpty(this.state.selectedBooking) ? null : this.state.selectedBooking.booking_number}</ModalHeader>
                    {
                        isEmpty(this.state.selectedBooking) ? null : (
                            <ModalBody>
                                <div className="row">
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <label>Company Name</label>
                                            <p>{this.state.selectedBooking.vendor_service.company.name}</p>
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <label>Service Name</label>
                                            <p>{this.state.selectedBooking.vendor_service.service.name}</p>
                                        </div>
                                    </div>
                                </div>

                                <hr/>

                                <div className="row">
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <label>Booking Date</label>
                                            <p>{this.state.selectedBooking.booking_date}</p>
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <label>Status</label>
                                            <div className="form-inline">
                                                <Select
                                                    className="basic-single w-50"
                                                    classNamePrefix="select"
                                                    options={this.state.statusOptions}
                                                    onChange={this.changeStatus}
                                                    value={this.state.statusOptions.find(option => option.value.toString() === this.state.bStatus.toString())}
                                                />
                                                <button onClick={this.updateStatus} className="btn btn-success ml-2">Update</button>
                                            </div>
                                            {/* <p>{bookingStatus[this.state.selectedBooking.status]}</p> */}
                                        </div>
                                    </div>
                                </div>

                                <hr/>

                                <div className="row">
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <label>Payment Type</label>
                                            <p>{paymentType[this.state.selectedBooking.payment_type]}</p>
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <label>Price</label>
                                            <p>{this.state.selectedBooking.price}</p>
                                        </div>
                                    </div>
                                </div>

                                <hr/>

                                <div className="row">
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <label>Service Tax</label>
                                            <p>{this.state.selectedBooking.service_tax}</p>
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <label>Transportation Fee</label>
                                            <p>{this.state.selectedBooking.shipping_fee}</p>
                                        </div>
                                    </div>
                                </div>

                                <hr/>

                                <div className="row">
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <label>Insurance</label>
                                            <p>{this.state.selectedBooking.insurance}</p>
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <label>Total Price</label>
                                            <p>{this.state.selectedBooking.total_price}</p>
                                        </div>
                                    </div>
                                </div>

                                <hr/>

                                <div className="row">
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <label>Promotion</label>
                                            <p>
                                                {
                                                    this.state.selectedBooking.promotion
                                                    ? this.state.selectedBooking.promotion.promo_code
                                                    : 'N/A'
                                                }
                                            </p>
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <label>Receipt</label>
                                            <p>
                                                {
                                                    this.state.selectedBooking.receipt || 'N/A'
                                                }
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <hr/>

                                <div className="row">
                                    <div className="col-md-6">
                                        <div className="form-group">
                                            <label>Refunded</label>
                                            <p>
                                                {
                                                    this.state.selectedBooking.status.toString() === '-1'
                                                    ? this.state.selectedBooking.refunded
                                                    : 'N/A'
                                                }
                                            </p>
                                        </div>
                                    </div>
                                </div>


                            </ModalBody>
                        )
                    }
                    <ModalFooter>
                        <button className="btn btn-secondary" onClick={(e) => this.toggleModal('isDetailModalOpen')}>Cancel</button>
                    </ModalFooter>
                </Modal>
            </div>
        )
    }
}

export default BookingListPage
