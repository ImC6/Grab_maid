import React from 'react'
import axios from 'axios'
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap'
import {
    swalSuccess,
    swalErr,
    isEmpty
} from '../../services/helper/utilities'
import config from '../../config'

class PromotionPage extends React.Component {
    constructor(props) {
        super(props)

        this.state = {
            ewalletList: [],
            isModalOpen: false,
            modalActionType: '',
            selectedService: {},
            editableSelectedLimit: {},
            id:''
        }

        this.getServices = this.getServices.bind(this)
        this.clearModalData = this.clearModalData.bind(this)
        this.selectService = this.selectService.bind(this)
        this.addService = this.addService.bind(this)
        this.saveService = this.saveService.bind(this)
        this.toggleModal = this.toggleModal.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.handleFileInputChange = this.handleFileInputChange.bind(this)
        this.removeLimit = this.removeLimit.bind(this)
    }

    componentWillMount() {
        this.getServices()
    }

    getServices() {
        axios.get(config.api.promotion)
        .then(res => {
            if (res.data.status === 200) {
                this.setState(prevState => {
                    return {
                        ewalletList: res.data.zones
                    }
                })
            }
        })
    }

    handleChange(e) {
        const property = e.target.id
        const value = e.target.value

        this.setState(prevState => {
            return {
                editableSelectedLimit: {
                    ...prevState.editableSelectedLimit,
                    [property]: value
                }
            }
        })
    }

    handleFileInputChange(e) {
        let property = e.target.id
        let file = e.target.files[0]
        let reader = new FileReader()
        reader.onload = () => {
            this.setState(prevState => {
                return {
                    editableSelectedLimit: {
                        ...prevState.editableSelectedLimit,
                        [property + '_preview']: reader.result
                    }
                }
            })
        }
        if (file) {
            reader.readAsDataURL(file)
            this.setState(prevState => {
                return {
                    editableSelectedLimit: {
                        ...prevState.editableSelectedLimit,
                        [property]: file
                    }
                }
            })
        }
    }

    selectService(e, service, id) {
        e.preventDefault()

        this.setState({
            isModalOpen: true,
            modalActionType: 'update',
            id:id,
            selectedService: {
                ...service,
            },
            editableSelectedLimit: {
                ...service,
            },
        })
    }

    addService() {
        this.setState({
            modalActionType: 'create',
            isModalOpen: true,
            selectedService: {
                promo_code: '',
                description: '',
                percentage: '',
                discount_type: '',
                total: '',
                status: '',
                start_date: '',
                end_date: '',
            },
            editableSelectedLimit: {
                promo_code: '',
                description: '',
                percentage: '',
                discount_type: '',
                total: '',
                status: '',
                start_date: '',
                end_date: '',
            },
        })
    }

    saveService() {
        let formData = new FormData()
        formData.append('promo_code', this.state.editableSelectedLimit.promo_code.trim())
        formData.append('description', this.state.editableSelectedLimit.description.trim())
        formData.append('percentage', this.state.editableSelectedLimit.percentage.trim())
        formData.append('discount_type', this.state.editableSelectedLimit.discount_type.trim())
        formData.append('total', this.state.editableSelectedLimit.total.trim())
        formData.append('status', this.state.editableSelectedLimit.status.trim())
        formData.append('start_date', this.state.editableSelectedLimit.start_date.trim())
        formData.append('end_date', this.state.editableSelectedLimit.end_date.trim())
    
        if (this.state.modalActionType === 'update') {
            axios.post(`${config.api.promotion}/update/${this.state.editableSelectedLimit.id}`, formData)
            .then(res => {
                if (res.data.status === 200) {
                    swalSuccess({
                        text: res.data.message
                    }).then(() => {
                        this.setState({
                            selectedService: {},
                            editableSelectedLimit: {},
                            isModalOpen: false,
                            modalActionType: ''
                        })
                        this.getServices()
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
                }
            })
        } 
        else if (this.state.modalActionType === 'create') {
            axios.post(config.api.promotion, formData)
            .then(res => {
                if (res.data.status === 200) {
                    swalSuccess({
                        text: res.data.message
                    }).then(() => {
                        this.setState({
                            selectedService: {},
                            editableSelectedLimit: {},
                            isModalOpen: false,
                            modalActionType: '',
                            
                        },window.location.reload())
                        this.getServices()
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
                }
                
            })
        } else {
            swalErr({
                text: 'Something went wrong'
            }).then(() => {
                window.location.reload()
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

    clearModalData() {
        this.setState({
            selectedService: {},
            editableSelectedLimit: {},
        })
    }

    editAddress(e, ewallet) {
        e.preventDefault()
        // this.getRegionList()
        // this.getLocationList()
        this.setState({
            isModalOpen1: true,
            modalActionType: 'update',
            selectedService: {
                id: ewallet.id,
                name: service.name,
                image: service.image_preview,
                
            },
            editableSelectedLimit: {
                id: service.id,
                name: service.name,
                image: service.image_preview,
                
            }
        })
    }

    removeLimit(ewallet) {
        axios.delete(`${config.api.promotion}/${ewallet.id}`).then(res => {
            if (res.status === 200) {
                swalSuccess({
                    text: 'Service is deleted'
                }).then(() => {
                    window.location.reload();
                });
            }
        });
    }



    render() {
        const list = this.state.ewalletList.map((ewallet, index) => {
            return (
                <tr key={index}>
                    <td>{index + 1}</td>
                    <td>{ewallet.promo_code}</td>
                    <td>{ewallet.description}</td>
                    <td>{ewallet.discount_type}</td>
                    <td>{ewallet.percentage}</td>
                    <td>{ewallet.by_amount}</td>
                    <td>{ewallet.to_amount}</td>
                    <td>{ewallet.total}</td>
                    <td>{ewallet.status}</td>
                    <td>{ewallet.start_date}</td>
                    <td>{ewallet.end_date}</td>

                    
                    <td>
                        
                        <a href="#" className="ml-2" onClick={e => this.selectService(e, ewallet,ewallet.id)}>
                                <i class="fas fa-edit"></i>
                        </a>
                        <button className="btn btn-xs color" onClick={() => this.removeLimit(ewallet)}>
                            <i className="far fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            )
        })

        return (
            <div id="user-list">
                <h3 className="ml-2">
                    List of Promotion Code

                    <button onClick={this.addService} className="btn btn-success btn-sm ml-2">Add Promo Code</button>
                </h3>

                <div className="table-responsive">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Promo Code</th>
                                <th>Detail</th>
                                <th>Discount_type</th>
                                <th>Percentage</th>
                                <th>By_amount</th>
                                <th>To_amount</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>End Date</th>

                                <th>
                                    <i className="fa fa-cogs"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {
                                list.length > 0
                                ? list
                                : (
                                    <tr>
                                        <td colSpan="5">List is empty!</td>
                                    </tr>
                                )
                            }
                        </tbody>
                    </table>
                    
                </div>

                <Modal isOpen={this.state.isModalOpen} toggle={(e) => this.toggleModal('isModalOpen')} onClosed={this.clearModalData}>
                    <ModalHeader toggle={(e) => this.toggleModal('isModalOpen')}>Service</ModalHeader>
                    {
                        isEmpty(this.state.editableSelectedLimit) ? null : (
                            <ModalBody>
                                <div className="form-group">
                                    <label>Promo Code</label>
                                    <input id="promo_code" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedLimit.promo_code} />
                                </div>
                                <div className="form-group">
                                    <label>Description</label>
                                    <input id="description" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedLimit.description} />
                                </div>
                                <div className="form-group">
                                    <label>Percentage</label>
                                    <input id="percentage" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedLimit.percentage} />
                                </div>
                                <div className="form-group">
                                    <label>Discount_type (1=Percentage,2=By_amount,3=To_amount)</label>
                                    <input id="discount_type" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedLimit.discount_type} />
                                </div>
                                <div className="form-group">
                                    <label>Total</label>
                                    <input id="total" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedLimit.total} />
                                </div>
                                <div className="form-group">
                                    <label>Status (1 = Valid, 0 = Invalid)</label>
                                    <input id="status" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedLimit.status} />
                                </div>
                                <div className="form-group">
                                    <label>Start Date</label>
                                    <input id="start_date" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedLimit.start_date} placeholder="yyyy-mm-dd" />
                                </div>
                                <div className="form-group">
                                    <label>End Date</label>
                                    <input id="end_date" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedLimit.end_date} placeholder="yyyy-mm-dd"/>
                                </div>
                            </ModalBody>
                        )
                    }
                    <ModalFooter>
                        <button onClick={this.saveService} className="btn btn-primary">Save</button>
                        <button className="btn btn-secondary" onClick={(e) => this.toggleModal('isModalOpen')}>Cancel</button>
                    </ModalFooter>
                </Modal>

                   

                
            </div>
        )
    }
}

export default PromotionPage
