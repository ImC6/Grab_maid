import React from 'react'
import axios from 'axios'
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap'
import {
    swalSuccess,
    swalErr,
    isEmpty
} from '../../services/helper/utilities'
import config from '../../config'

class ServicePage extends React.Component {
    constructor(props) {
        super(props)

        this.state = {
            serviceList: [],
            isModalOpen: false,
            modalActionType: '',
            selectedService: {},
            editableSelectedService: {},
        }

        this.getServices = this.getServices.bind(this)
        this.clearModalData = this.clearModalData.bind(this)
        this.selectService = this.selectService.bind(this)
        this.addService = this.addService.bind(this)
        this.saveService = this.saveService.bind(this)
        this.toggleModal = this.toggleModal.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.handleFileInputChange = this.handleFileInputChange.bind(this)
        this.removeService = this.removeService.bind(this)
    }

    componentWillMount() {
        this.getServices()
    }

    getServices() {
        axios.get(config.api.services)
        .then(res => {
            if (res.data.status === 200) {
                this.setState(prevState => {
                    return {
                        serviceList: res.data.services
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
                editableSelectedService: {
                    ...prevState.editableSelectedService,
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
                    editableSelectedService: {
                        ...prevState.editableSelectedService,
                        [property + '_preview']: reader.result
                    }
                }
            })
        }
        if (file) {
            reader.readAsDataURL(file)
            this.setState(prevState => {
                return {
                    editableSelectedService: {
                        ...prevState.editableSelectedService,
                        [property]: file
                    }
                }
            })
        }
    }

    selectService(e, service) {
        e.preventDefault()

        this.setState({
            isModalOpen: true,
            modalActionType: 'update',
            selectedService: {
                ...service,
                image_preview: service.image || 'https://via.placeholder.com/50',
            },
            editableSelectedService: {
                ...service,
                image_preview: service.image || 'https://via.placeholder.com/50',
            },
        })
    }

    addService() {
        this.setState({
            modalActionType: 'create',
            isModalOpen: true,
            selectedService: {
                name: '',
                details: '',
                image: '',
                image_preview: 'https://via.placeholder.com/50'
            },
            editableSelectedService: {
                name: '',
                details: '',
                image: '',
                image_preview: 'https://via.placeholder.com/50'
            },
        })
    }

    saveService() {
        let formData = new FormData()
        formData.append('name', this.state.editableSelectedService.name.trim())
        formData.append('details', this.state.editableSelectedService.details.trim())
        formData.append('image', this.state.editableSelectedService.image)

        if (this.state.modalActionType === 'update') {
            axios.post(`${config.api.services}/update/${this.state.editableSelectedService.id}`, formData)
            .then(res => {
                if (res.data.status === 200) {
                    swalSuccess({
                        text: res.data.message
                    }).then(() => {
                        this.setState({
                            selectedService: {},
                            editableSelectedService: {},
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
        } else if (this.state.modalActionType === 'create') {
            axios.post(config.api.services, formData)
            .then(res => {
                if (res.data.status === 200) {
                    swalSuccess({
                        text: res.data.message
                    }).then(() => {
                        this.setState({
                            selectedService: {},
                            editableSelectedService: {},
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
            editableSelectedService: {},
        })
    }

    editAddress(e, service) {
        e.preventDefault()
        // this.getRegionList()
        // this.getLocationList()
        this.setState({
            isModalOpen1: true,
            modalActionType: 'update',
            selectedService: {
                id: service.id,
                name: service.name,
                image: service.image_preview,
                detail: service.detail
            },
            editableSelectedService: {
                id: service.id,
                name: service.name,
                image: service.image_preview,
                detail: service.detail
            }
        })
    }

    removeService(service) {
        axios.delete(`${config.api.services}/${service.id}`).then(res => {
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
        const list = this.state.serviceList.map((service, index) => {
            return (
                <tr key={index}>
                    <td>{index + 1}</td>
                    <td>
                        <a href="#" onClick={e => this.selectService(e, service)}>{service.name}</a>
                    </td>
                    <td>
                        {
                            service.image ?
                            (
                                <img src={service.image} className="rounded-circle" width="20" height="20" alt={service.name} />
                            ) :
                            service.name
                        }
                    </td>
                    <td>{service.details}</td>
                    <td>{service.created_at}</td>
                    <td>
                        <a href="#" className="ml-2" onClick={e => this.editService(e, service)}>
                            <i class="fas fa-edit"></i>
                        </a>
                        <button className="btn btn-xs color" onClick={() => this.removeService(service)}>
                            <i className="far fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            )
        })

        return (
            <div id="user-list">
                <h3 className="ml-2">
                    List of Services

                    <button onClick={this.addService} className="btn btn-success btn-sm ml-2">Add service</button>
                </h3>

                <div className="table-responsive">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Details</th>
                                <th>Created At</th>
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
                        isEmpty(this.state.editableSelectedService) ? null : (
                            <ModalBody>
                                <div className="text-center">
                                    <a href="#" onClick={() => this.imageInput.click()}>
                                        <img src={this.state.editableSelectedService.image_preview} className="rounded" />
                                    </a>
                                    <input id="image" type="file" className={`d-none form-control`} onChange={this.handleFileInputChange} ref={(el) => this.imageInput = el}/>
                                    <p><small>Image size: 50 x 50</small></p>
                                </div>
                                <div className="form-group">
                                    <label>Name</label>
                                    <input id="name" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedService.name} />
                                </div>
                                <div className="form-group">
                                    <label>Description</label>
                                    <input id="details" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedService.details} />
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

export default ServicePage
