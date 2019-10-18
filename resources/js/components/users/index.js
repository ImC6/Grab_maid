import React from 'react'
import axios from 'axios'
import { Link } from 'react-router-dom'
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap'
import {
    isEmpty,
    capitalize,
    swalSuccess,
    swalErr
} from '../../services/helper/utilities'
import config from '../../config'

class UserPage extends React.Component {
    constructor(props) {
        super(props)

        this.state = {
            selectedRole: 4,
            userList: [],
            isUserModalOpen: false,
            isPwModalOpen: false,
            selectedUser: {},
            editableSelectedUser: {},
            addUserType: '',
            saveUserAction: '',
            new_password: '',
            new_password_confirmation: ''
        }

        this.getUserList = this.getUserList.bind(this)
        this.clearModalData = this.clearModalData.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.handleChangeRole = this.handleChangeRole.bind(this)
        this.handleChangePw = this.handleChangePw.bind(this)
        this.selectUser = this.selectUser.bind(this)        
        this.addUser = this.addUser.bind(this)
        this.saveUser = this.saveUser.bind(this)
        this.removeUser = this.removeUser.bind(this)
        this.changePw = this.changePw.bind(this)
        this.savePassword = this.savePassword.bind(this)
        this.toggleModal = this.toggleModal.bind(this)
    }

    componentWillMount() {
        this.getUserList()
    }

    getUserList() {
        axios.get(config.api.users.base, {
            params: {
                role: this.state.selectedRole
            }
        }).then(res => {
            if (res.data.status === 200) {
                this.setState({
                    userList: res.data.users
                })
            }
        })
    }

    handleChangeRole(e, action = null) {
        const name = e.target.id;
        this.setState({ [name]: e.target.value }, function() {
            if (action && action === 'update') {
                this.getUserList();
            }
        });
    }

    handleChange(e) {
        const name = e.target.id;
        const value = e.target.value;
        this.setState(prevState => {
            return {
                editableSelectedUser: {
                    ...prevState.editableSelectedUser,
                    [name]: value
                }
            }
        });
    }

    handleChangePw(e) {
        const name = e.target.id
        const value = e.target.value
        this.setState({
            [name]: value
        });
    }

    selectUser(e, service, id) {
        e.preventDefault()

        this.setState({
            isUserModalOpen: true,
            saveUserAction: 'update',
            id:id,
            selectedService: {
                ...service,
            },
            editableSelectedUser: {
                ...service,
            },
        })
    }

    addUser(e, userType) {
        e.preventDefault()

        this.setState(prevState => {
            let userObj = {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                mobile_no: '',
                gender: '',
            }

            return {
                isUserModalOpen: true,
                saveUserAction: 'create',
                addUserType: userType,
                selectedUser: userObj,
                editableSelectedUser: userObj
            }
        })
    }

    saveUser(e) {
        e.preventDefault()
        let params = {
            name: this.state.editableSelectedUser.name,
            email: this.state.editableSelectedUser.email,
            password: this.state.editableSelectedUser.password,
            password_confirmation: this.state.editableSelectedUser.password_confirmation,
            mobile_no: this.state.editableSelectedUser.mobile_no,
            gender: this.state.editableSelectedUser.gender,
        }

        let url = `${config.api.users.base}/${this.state.addUserType}`

        switch (this.state.saveUserAction) {
            case 'create':
                if (this.state.addUserType === '') {
                    swalErr({
                        text: 'Something went wrong'
                    }).then(() => {
                        window.location.reload()
                    })
                    return false
                }

                axios.post(url, params)
                .then(res => {
                    if (res.data.status === 200) {
                        swalSuccess({
                            text: res.data.message
                        }).then(() => {
                            this.setState({
                                isUserModalOpen: false,
                                selectedUser: {},
                                editableSelectedUser: {}
                            })
                            this.getUserList()
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
                        swalErr()
                    }
                })
                break;

            case 'update':
                    axios.post(`${config.api.users.base}/update/${this.state.editableSelectedUser.id}`, params)
                    .then(res => {
                        if (res.data.status === 200) {
                            swalSuccess({
                                text: res.data.message
                            }).then(() => {
                                this.setState({
                                    isUserModalOpen: false,
                                    selectedUser: {},
                                    editableSelectedUser: {}
                                })
                                this.getUserList()
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
                            swalErr()
                        }
                    })
                break;

            default:
                swalErr({
                    text: 'Something went wrong'
                }).then(() => {
                    window.location.reload()
                })
                break;
        }
    }

    changePw(e, user) {
        e.preventDefault()

        this.setState({
            isPwModalOpen: true,
            selectedUser: user
        })
    }

    savePassword(e) {
        e.preventDefault()

        const params = {
            password: this.state.new_password,
            password_confirmation: this.state.new_password_confirmation,
        }
        axios.put(`${config.api.users.password}/${this.state.selectedUser.guid}`, params)
        .then(res => {
            if (res.data.status === 200) {
                swalSuccess({
                    text: res.data.message
                }).then(() => {
                    this.setState({
                        isPwModalOpen: false,
                        selectedUser: {},
                        new_password: '',
                        new_password_confirmation: ''
                    })
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
                swalErr({
                    text: res.data.message
                }).then(() => {
                    window.location.reload()
                })
            }
        })
    }

    removeUser(user) {
        axios.delete(`${config.api.users.base}/${user.id}`).then(res => {
            if (res.status === 200) {
                swalSuccess({
                    text: 'Service is deleted'
                }).then(() => {
                    window.location.reload();
                });
            }
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
            selectedUser: {},
            editableSelectedUser: {},
            new_password: '',
            new_password_confirmation: ''
        })
    }

    render() {
        const list = this.state.userList.map((user, index) => {
            return (
                <tr key={index}>
                    <td>{index + 1}</td>
                    <td>{user.email}</td>
                    <td>{user.name}</td>
                    <td>{user.updated_at}</td>
                    <td>{user.created_at}</td>
                    <td>
                        <Link className={`${this.state.selectedRole.toString() === '4' ? '' : 'd-none'}`} to={`${config.paths.settings.addresses}/${user.guid}`}>
                            <i className="fas fa-address-card"></i>
                        </Link>
                        <a href="#" className="ml-2" onClick={e => this.selectUser(e, user,user.id)}>
                                <i class="fas fa-edit"></i>
                        </a>
                        <a href="#" className="ml-2" onClick={() => this.removeUser(user)}>
                            <i className="far fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
            )
        })

        return (
            <div id="user-list">
                <h3>
                    List of
                </h3>

                <div className="row mb-2">
                    <div className="col-sm-3">
                        <select id="selectedRole" onChange={(e) => this.handleChangeRole(e, 'update')} value={this.state.selectedRole} className="form-control form-control-sm">
                            <option value="4">User</option>
                            <option value="2">Vendor</option>
                            <option value="3">Cleaner</option>
                        </select>
                    </div>
                    <div className="col-sm-3">
                        <div className="dropdown">
                            <button className="btn btn-success btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Add New
                            </button>
                            <div className="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a className="dropdown-item" href="#" onClick={e => this.addUser(e, 'user')}>New User</a>
                                <a className="dropdown-item" href="#" onClick={e => this.addUser(e, 'vendor')}>New Vendor</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="table-responsive">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Last Login</th>
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

                <Modal isOpen={this.state.isUserModalOpen} toggle={(e) => this.toggleModal('isUserModalOpen')} onClosed={this.clearModalData}>
                    <ModalHeader toggle={(e) => this.toggleModal('isUserModalOpen')}>New { capitalize(this.state.addUserType) }</ModalHeader>
                    {
                        isEmpty(this.state.editableSelectedUser) ? null : (
                            <ModalBody>
                                <div className="form-group">
                                    <label>Name</label>
                                    <input id="name" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedUser.name} />
                                </div>
                                <div className="form-group">
                                    <label>Email</label>
                                    <input id="email" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedUser.email} />
                                </div>
                                <div className="form-group">
                                    <label>Password</label>
                                    <input id="password" type="password" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedUser.password} />
                                </div>
                                <div className="form-group">
                                    <label>Confirm Password</label>
                                    <input id="password_confirmation" type="password" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedUser.password_confirmation} />
                                </div>
                                <div className="form-group">
                                    <label>Mobile Number</label>
                                    <input id="mobile_no" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedUser.mobile_no} />
                                </div>
                                <div className="form-group">
                                    <label>Gender</label>
                                    <select id="gender" className={`form-control`} onChange={this.handleChange} value={this.state.editableSelectedUser.gender}>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                            </ModalBody>
                        )
                    }
                    <ModalFooter>
                        <button onClick={this.saveUser} className="btn btn-primary">Save</button>
                        <button className="btn btn-secondary" onClick={(e) => this.toggleModal('isUserModalOpen')}>Cancel</button>
                    </ModalFooter>
                </Modal>

                <Modal isOpen={this.state.isPwModalOpen} toggle={(e) => this.toggleModal('isPwModalOpen')} onClosed={this.clearModalData}>
                    <ModalHeader toggle={(e) => this.toggleModal('isPwModalOpen')}>{this.state.selectedUser.name || 'Change Password'}</ModalHeader>
                        <ModalBody>
                            <div className="form-group">
                                <label>New Password</label>
                                <input id="new_password" type="password" className={`form-control`} onChange={this.handleChangePw} value={this.state.new_password} />
                            </div>
                            <div className="form-group">
                                <label>Confirm New Password</label>
                                <input id="new_password_confirmation" type="password" className={`form-control`} onChange={this.handleChangePw} value={this.state.new_password_confirmation} />
                            </div>
                        </ModalBody>
                    <ModalFooter>
                        <button onClick={this.savePassword} className="btn btn-primary">Save</button>
                        <button className="btn btn-secondary" onClick={(e) => this.toggleModal('isPwModalOpen')}>Cancel</button>
                    </ModalFooter>
                </Modal>
            </div>
        )
    }
}

export default UserPage
