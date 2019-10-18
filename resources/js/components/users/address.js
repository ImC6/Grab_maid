import React from 'react'
import axios from 'axios'
import { Link } from 'react-router-dom'
import Select from 'react-select'
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap'
import config from '../../config'
import { swalSuccess, isEmpty } from '../../services/helper/utilities';

class UserAddressPage extends React.Component {
    constructor(props) {
        super(props)

        this.state = {
            addressList: [],
            user: {},
            selectedAddress: {},
            editableSelectedAddress: {},
            isModalOpen: false,
            modalActionType: '',
            regionList: [],
            stateList: []
        }

        this.getAddressList = this.getAddressList.bind(this)
        // this.getRegionList = this.getRegionList.bind(this)
        // this.getStateList = this.getStateList.bind(this)
        this.getLocationList = this.getLocationList.bind(this)
        this.addAddress = this.addAddress.bind(this)
        this.editAddress = this.editAddress.bind(this)
        this.saveAddress = this.saveAddress.bind(this)
        this.toggleModal = this.toggleModal.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.handleChangeSelect = this.handleChangeSelect.bind(this)
    }

    componentWillMount() {
        this.getAddressList()
    }

    getAddressList() {
        axios.get(`${config.api.users.addresses}/${this.props.match.params.guid}`)
        .then(res => {
            if (res.data.status === 200) {
                const user = {
                    id: res.data.user.id,
                    name: res.data.user.name
                }
                this.setState({
                    user: user,
                    addressList: res.data.user.addresses || []
                })
            }
        })
    }

    // getRegionList() {
    //     if (this.state.regionList.length === 0) {
    //         axios.get(config.api.regions)
    //         .then(res => {
    //             if (res.data.status === 200) {
    //                 const regions = res.data.regions.map(region => {
    //                     return {
    //                         label: region,
    //                         value: region,
    //                     }
    //                 })

    //                 this.setState({
    //                     regionList: regions
    //                 })
    //             }
    //         })
    //     }
    // }

    // getStateList() {
    //     if (this.state.stateList.length === 0) {
    //         axios.get(config.api.states)
    //         .then(res => {
    //             if (res.data.status === 200) {
    //                 const states = res.data.states.map(state => {
    //                     return {
    //                         label: state,
    //                         value: state,
    //                     }
    //                 })

    //                 this.setState({
    //                     stateList: states
    //                 })
    //             }
    //         })
    //     }
    // }

    getLocationList() {
        if (this.state.stateList.length === 0 || this.state.regionList.length === 0) {
            axios.get(config.api.locations)
            .then(res => {
                if (res.data.status === 200) {
                    const states = res.data.states.map(state => {
                        return {
                            label: state,
                            value: state,
                        }
                    })

                    const regions = res.data.regions.map(state => {
                        return {
                            label: state,
                            value: state,
                        }
                    })

                    this.setState({
                        regionList: regions,
                        stateList: states
                    })
                }
            })
        }
    }

    addAddress() {
        // this.getRegionList()
        // this.getStateList()
        this.getLocationList()

        this.setState({
            isModalOpen: true,
            modalActionType: 'create',
            selectedAddress: {},
            editableSelectedAddress: {
                house_no: '',
                address_line: '',
                postcode: '',
                region: '',
                state: ''
            }
        })
    }

    editAddress(e, address) {
        e.preventDefault()
        // this.getRegionList()
        this.getLocationList()
        this.setState({
            isModalOpen: true,
            modalActionType: 'update',
            selectedAddress: {
                id: address.id,
                house_no: address.house_no,
                address_line: address.address_line,
                postcode: address.postcode,
                region: address.region,
                state: address.state
            },
            editableSelectedAddress: {
                house_no: address.house_no,
                address_line: address.address_line,
                postcode: address.postcode,
                region: address.region,
                state: address.state
            }
        })
    }

    deleteAddress(address) {
        axios.delete(`${config.api.users.base}/${address.guid}`).then(res => {
            if (res.status === 200) {
                swalSuccess({
                    text: 'User is deleted'
                }).then(() => {
                    window.location.reload();
                });
            }
        });
    }

    saveAddress() {
        const params = {
            house_no: this.state.editableSelectedAddress.house_no,
            address_line: this.state.editableSelectedAddress.address_line,
            postcode: this.state.editableSelectedAddress.postcode,
            region: this.state.editableSelectedAddress.region,
            state: this.state.editableSelectedAddress.state
        }

        if (this.state.modalActionType === 'create') {
            axios.post(`${config.api.users.addresses}/${this.props.match.params.guid}`, params)
            .then(res => {
                if (res.data.status === 200) {
                    swalSuccess({
                        text: res.data.message
                    }).then(() => {
                        this.getAddressList()
                        this.setState({
                            isModalOpen: false,
                            modalActionType: '',
                            selectedAddress: {},
                            editableSelectedAddress: {},
                        })
                    })
                }
            })
        } else if (this.state.modalActionType === 'update') {
            axios.put(`${config.api.users.addresses}/${this.state.selectedAddress.id}`, params)
            .then(res => {
                if (res.data.status === 200) {
                    swalSuccess({
                        text: res.data.message
                    }).then(() => {
                        this.getAddressList()
                        this.setState({
                            isModalOpen: false,
                            modalActionType: '',
                            selectedAddress: {},
                            editableSelectedAddress: {},
                        })
                    })
                }
            })
        }
    }

    toggleModal(modal) {
        this.setState(prevState => {
            return {
                [modal]: !prevState[modal]
            }
        })
    }

    handleChange(e) {
        const property = e.target.id
        const value = e.target.value
        this.setState(prevState => {
            return {
                editableSelectedAddress: {
                    ...prevState.editableSelectedAddress,
                    [property]: value
                }
            }
        })
    }

    handleChangeSelect(selectOption, {name}) {
        this.setState(prevState => {
            return {
                editableSelectedAddress: {
                    ...prevState.editableSelectedAddress,
                    [name]: selectOption.value
                }
            }
        })
    }

    
    removeAddress(address) {
        axios.delete(`${config.api.users.addresses}/${address.id}`).then(res => {
            if (res.status === 200) {
                swalSuccess({
                    text: 'User is deleted'
                }).then(() => {
                    window.location.reload();
                });
            }
        });
    }

    render() {
        const list = this.state.addressList.map((address, index) => {
            return (
                <tr key={index}>
                    <td>{index + 1}</td>
                    <td>{address.house_no}</td>
                    <td>{address.address_line}</td>
                    <td>{address.postcode}</td>
                    <td>{address.region}</td>
                    <td>{address.state}</td>
                    <td>{address.created_at}</td>
                    <td>
                        <a href="#" onClick={e => this.editAddress(e, address)}>
                            <i className="far fa-edit"></i>
                        </a>
                        <a href="#" onClick={e => this.deleteAddress(e, address)}>
                            <i className="far fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
            )
        })

        return (
            <div id="address-list">
                <h3 className="ml-2">
                    {this.state.user.name || ''} addresses
                    <button className="btn btn-sm btn-success ml-2" onClick={this.addAddress}>Add address</button>
                </h3>

                <div className="table-responsive">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>House No</th>
                                <th>Address Line</th>
                                <th>Postcode</th>
                                <th>Region</th>
                                <th>State</th>
                                <th>Created At</th>
                                <th>
                                    <i className="fas fa-cog"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {list.length > 0 ? list : (
                                <tr>
                                    <td colSpan="5">List is empty!</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>


                <Modal isOpen={this.state.isModalOpen} toggle={(e) => this.toggleModal('isModalOpen')} onClosed={this.clearModalData}>
                    <ModalHeader toggle={(e) => this.toggleModal('isModalOpen')}>Address</ModalHeader>
                    {
                        isEmpty(this.state.editableSelectedAddress) ? null : (
                            <ModalBody>
                                <div className="form-group">
                                    <label>House No</label>
                                    <input id="house_no" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedAddress.house_no} />
                                </div>
                                <div className="form-group">
                                    <label>Address Line</label>
                                    <input id="address_line" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedAddress.address_line} />
                                </div>
                                <div className="form-group">
                                    <label>Postcode</label>
                                    <input id="postcode" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedAddress.postcode} />
                                </div>
                                <div className="form-group">
                                    <label>Region</label>
                                    <Select
                                        value={this.state.editableSelectedAddress.region ? {
                                            label: this.state.editableSelectedAddress.region,
                                            value: this.state.editableSelectedAddress.region
                                        } : {}}
                                        name="region"
                                        options={this.state.regionList}
                                        className="select"
                                        onChange={this.handleChangeSelect}
                                    />
                                </div>
                                <div className="form-group">
                                    <label>State</label>
                                    <Select
                                        value={this.state.editableSelectedAddress.state ? {
                                            label: this.state.editableSelectedAddress.state,
                                            value: this.state.editableSelectedAddress.state
                                        } : {}}
                                        name="state"
                                        options={this.state.stateList}
                                        className="select"
                                        onChange={this.handleChangeSelect}
                                    />
                                </div>
                            </ModalBody>
                        )
                    }
                    <ModalFooter>
                        <button onClick={this.saveAddress} className="btn btn-primary">Save</button>
                        <button className="btn btn-secondary" onClick={(e) => this.toggleModal('isModalOpen')}>Cancel</button>
                    </ModalFooter>
                </Modal>
            </div>
        )
    }
}

export default UserAddressPage
