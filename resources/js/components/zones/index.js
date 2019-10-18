import React from 'react'
import axios from 'axios'
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap'
import {
    swalSuccess,
    swalErr,
    isEmpty
} from '../../services/helper/utilities'
import config from '../../config'

class Ewalletpage extends React.Component {
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

        this.getZone = this.getZone.bind(this)
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
        this.getZone()
    }

    getZone() {
        axios.get(config.api.zone)
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
                postcode: '',
                area: '',
                region: '',
                city: '',
                state: '',
            },
            editableSelectedLimit: {
                postcode: '',
                area: '',
                region: '',
                city: '',
                state: '',
            },
        })
    }

    saveService() {
        let formData = new FormData()
        formData.append('postcode', this.state.editableSelectedLimit.postcode.trim())
        formData.append('area', this.state.editableSelectedLimit.area.trim())
        formData.append('region', this.state.editableSelectedLimit.region.trim())
        formData.append('city', this.state.editableSelectedLimit.city.trim())
        formData.append('state', this.state.editableSelectedLimit.state.trim())
    
        if (this.state.modalActionType === 'update') {
            axios.post(`${config.api.zone}/update/${this.state.editableSelectedLimit.id}`, formData)
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
            axios.post(config.api.zone, formData)
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
        axios.delete(`${config.api.zone}/${ewallet.id}`).then(res => {
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
                    <td>{ewallet.postcode}</td>
                    <td>{ewallet.area}</td>
                    <td>{ewallet.region}</td>
                    <td>{ewallet.city}</td>
                    <td>{ewallet.state}</td>

                    
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
                    List of Zones

                    <button onClick={this.addService} className="btn btn-success btn-sm ml-2">Add Zone</button>
                </h3>

                <div className="table-responsive">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Postcode</th>
                                <th>Detail</th>
                                <th>Region</th>
                                <th>City</th>
                                <th>State</th>
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
                                    <label>Postcode</label>
                                    <input id="postcode" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedLimit.postcode} />
                                </div>
                                <div className="form-group">
                                    <label>Detail</label>
                                    <input id="area" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedLimit.area} />
                                </div>
                                <div className="form-group">
                                    <label>Region</label>
                                    <input id="region" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedLimit.region} />
                                </div>
                                <div className="form-group">
                                    <label>City</label>
                                    <input id="city" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedLimit.city} />
                                </div>
                                <div className="form-group">
                                    <label>State</label>
                                    <input id="state" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedLimit.state} />
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

export default Ewalletpage
