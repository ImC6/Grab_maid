import React from 'react'
import axios from 'axios'
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap'
import {
    swalSuccess,
    swalErr
} from '../../services/helper/utilities'
import config from '../../config'

class VendorCleanerPage extends React.Component {
    constructor(props) {
        super(props)

        this.state = {
            cleanerList: [],
            isUpdateModalOpen: false,
            isCreateModalOpen: false,
            selectedCleaner: {},
            editableSelectedCleaner: {},
            saveCleanerAction: '',
            vendor: {},
        }

        this.getCleanerList = this.getCleanerList.bind(this)
        this.toggleModal = this.toggleModal.bind(this)
        this.selectCleaner = this.selectCleaner.bind(this)
        this.createCleaner = this.createCleaner.bind(this)
        this.saveCleaner = this.saveCleaner.bind(this)
        this.clearModalData = this.clearModalData.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.handleFileInputChange = this.handleFileInputChange.bind(this)
    }

    componentDidMount() {
        this.getCleanerList()
    }

    getCleanerList() {
        axios.get(`${config.api.vendor.cleaner}/${this.props.match.params.guid}`)
        .then(res => {
            if (res.data.status === 200) {
                this.setState({
                    vendor: res.data.vendor,
                    cleanerList: res.data.cleaners
                })
            }
        })
    }

    selectCleaner(e, cleaner) {
        e.preventDefault()
        this.setState(prevState => {
            return {
                selectedCleaner: {
                    guid: cleaner.guid,
                    name: cleaner.name,
                    mobile_no: cleaner.mobile_no || '',
                    gender: cleaner.gender || '',
                    // profile_picture_preview: cleaner.profile_picture || 'https://via.placeholder.com/150',
                },
                editableSelectedCleaner: {
                    guid: cleaner.guid,
                    name: cleaner.name,
                    mobile_no: cleaner.mobile_no || '',
                    gender: cleaner.gender || '',
                    // profile_picture_preview: cleaner.profile_picture || 'https://via.placeholder.com/150',
                },
                isUpdateModalOpen: !prevState.isUpdateModalOpen,
                saveCleanerAction: 'update'
            }
        })
    }

    createCleaner(e) {
        this.toggleModal('isCreateModalOpen')
        this.setState({
            editableSelectedCleaner: {
                name: '',
                email: '',
                password: '',
                mobile_no: '',
                gender: '',
                // profile_picture: '',
                // profile_picture_preview: 'https://via.placeholder.com/150',
            },
            saveCleanerAction: 'create'
        })
    }

    saveCleaner(e) {
        let url = ''
        let params = {
            name: this.state.editableSelectedCleaner.name,
            mobile_no: this.state.editableSelectedCleaner.mobile_no,
            gender: this.state.editableSelectedCleaner.gender,
        }

        if (this.state.saveCleanerAction === 'create') {
            url = `${config.api.users.base}/cleaner`
            params.vendor_guid = this.props.match.params.guid
            params.email = this.state.editableSelectedCleaner.email
            params.password = this.state.editableSelectedCleaner.password
            params.password_confirmation = this.state.editableSelectedCleaner.password
        } else if (this.state.saveCleanerAction === 'update') {
            url = config.api.users.profileUpdate
            params.user_guid = this.state.editableSelectedCleaner.guid
        } else {
            swalErr({
                text: 'Something went wrong'
            }).then(() => {
                window.location.reload()
            })
        }

        axios.post(url, params)
        .then(res => {
            if (res.data.status === 200) {
                swalSuccess({
                    text: res.data.message
                }).then(() => {
                    this.setState({
                        isUpdateModalOpen: false,
                        isCreateModalOpen: false,
                        selectCleaner: {},
                        editableSelectedCleaner: {},
                        saveCleanerAction: '',
                    })
                    this.getCleanerList()
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

    handleChange(e) {
        const property = e.target.id
        const value = e.target.value
        this.setState(prevState => {
            return {
                editableSelectedCleaner: {
                    ...prevState.editableSelectedCleaner,
                    [property]: value
                }
            }
        })
    }

    handleFileInputChange(e) {
        const property = e.target.id
        const file = e.target.files[0]
        let reader = new FileReader()
        reader.onload = () => {
            this.setState(prevState => {
                return {
                    editableSelectedCleaner: {
                        ...prevState.editableSelectedCleaner,
                        [property + '_preview']: reader.result
                    }
                }
            })
        }
        if (file) {
            reader.readAsDataURL(file)
            this.setState(prevState => {
                return {
                    editableSelectedCleaner: {
                        ...prevState.editableSelectedCleaner,
                        [property]: file
                    }
                }
            })
        }
    }

    clearModalData() {
        this.setState({
            selectedCleaner: {},
            editableSelectedCleaner: {}
        })
    }

    toggleModal(modal) {
        this.setState(prevState => {
            return {
                [modal]: !prevState[modal]
            }
        })
    }

    render() {
        const cleanerList = this.state.cleanerList.map((cleaner, index) => {
            return (
                <tr key={index}>
                    <td>{index + 1}</td>
                    <td>
                        <a href="#" onClick={(e) => this.selectCleaner(e, cleaner)}>
                            {cleaner.name}
                        </a>
                    </td>
                    <td>{cleaner.mobile_no}</td>
                    <td>{cleaner.gender}</td>
                    <td>{cleaner.created_at}</td>
                </tr>
            )
        })

        return (
            <div id="vendor-management">
                <h3>
                    <a href="#" className="btn btn-link" onClick={this.props.history.goBack}>
                        <i className="fas fa-chevron-left"></i>
                    </a>
                    <span className="ml-2">{this.state.vendor.name || ''}'s Cleaners</span>
                    <button onClick={this.createCleaner} className="btn btn-success btn-sm ml-2">Add cleaner</button>
                </h3>

                <div className="table-responsive">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Mobile No</th>
                                <th>Gender</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            {cleanerList.length > 0 ? cleanerList : (
                                <tr>
                                    <td colSpan="5">List is empty!</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Modal isOpen={this.state.isUpdateModalOpen} toggle={(e) => this.toggleModal('isUpdateModalOpen')} onClosed={this.clearModalData}>
                    <ModalHeader toggle={(e) => this.toggleModal('isUpdateModalOpen')}>Cleaner {this.state.selectedCleaner.name}</ModalHeader>
                    <ModalBody>
                        {/* <div className="text-center">
                            <a href="#" onClick={() => this.imageInput.click()}>
                                <img src={this.state.editableSelectedCleaner.profile_picture_preview} className="rounded" />
                            </a>
                            <input id="profile_picture" type="file" className={`d-none form-control`} onChange={this.handleFileInputChange} ref={(el) => this.imageInput = el}/>
                            <p><small>Image size: 150 x 150</small></p>
                        </div> */}
                        <div className="form-group">
                            <label>Name</label>
                            <input id="name" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedCleaner.name} />
                        </div>
                        <div className="form-group">
                            <label>Mobile No</label>
                            <input id="mobile_no" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedCleaner.mobile_no} />
                        </div>
                        <div className="form-group">
                            <label>Gender</label>
                            <select id="gender" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedCleaner.gender}>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <button onClick={this.saveCleaner} className="btn btn-primary">Save</button>
                        <button className="btn btn-secondary" onClick={(e) => this.toggleModal('isUpdateModalOpen')}>Cancel</button>
                    </ModalFooter>
                </Modal>

                <Modal isOpen={this.state.isCreateModalOpen} toggle={(e) => this.toggleModal('isCreateModalOpen')} onClosed={this.clearModalData}>
                    <ModalHeader toggle={(e) => this.toggleModal('isCreateModalOpen')}>Cleaner {this.state.selectedCleaner.name}</ModalHeader>
                    <ModalBody>
                        {/* <div className="text-center">
                            <a href="#" onClick={() => this.imageInput.click()}>
                                <img src={this.state.editableSelectedCleaner.profile_picture_preview} className="rounded" />
                            </a>
                            <input id="profile_picture" type="file" className={`d-none form-control`} onChange={this.handleFileInputChange} ref={(el) => this.imageInput = el}/>
                            <p><small>Image size: 150 x 150</small></p>
                        </div> */}
                        <div className="form-group">
                            <label>Name</label>
                            <input id="name" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedCleaner.name} />
                        </div>
                        <div className="form-group">
                            <label>Email</label>
                            <input id="email" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedCleaner.email} />
                        </div>
                        <div className="form-group">
                            <label>Password</label>
                            <input id="password" type="password" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedCleaner.password} />
                        </div>
                        <div className="form-group">
                            <label>Mobile No</label>
                            <input id="mobile_no" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedCleaner.mobile_no} />
                        </div>
                        <div className="form-group">
                            <label>Gender</label>
                            <select id="gender" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedCleaner.gender}>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <button onClick={this.saveCleaner} className="btn btn-primary">Save</button>
                        <button className="btn btn-secondary" onClick={(e) => this.toggleModal('isCreateModalOpen')}>Cancel</button>
                    </ModalFooter>
                </Modal>
            </div>
        )
    }
}

export default VendorCleanerPage
