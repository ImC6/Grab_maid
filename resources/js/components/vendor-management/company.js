import React from 'react'
import axios from 'axios'
import { Link } from 'react-router-dom'
import Select from 'react-select'
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap'
import {
    swalErr,
    swalSuccess
} from '../../services/helper/utilities'
import config from '../../config'

class VendorCompanyPage extends React.Component {
    constructor(props) {
        super(props)

        this.state = {
            companyList: [],
            isCompanyModalOpen: false,
            isBankModalOpen: false,
            selectedCompany: {},
            editableSelectedCompany: {},
            selectedBank: {},
            editableSelectedBank: {},
            bankModalAction: '',
            companyModalAction: '',
            regionList: [],
            stateList: [],
            vendor: {},
        }

        this.getCompanyList = this.getCompanyList.bind(this)
        this.getLocationList = this.getLocationList.bind(this)
        this.toggleModal = this.toggleModal.bind(this)
        this.selectCompany = this.selectCompany.bind(this)
        this.createCompany = this.createCompany.bind(this)
        this.saveCompany = this.saveCompany.bind(this)
        this.saveBank = this.saveBank.bind(this)
        this.showCompanyBank = this.showCompanyBank.bind(this)
        this.clearModalData = this.clearModalData.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.handleChangeSelect = this.handleChangeSelect.bind(this)
        this.handleFileInputChange = this.handleFileInputChange.bind(this)
    }

    componentDidMount() {
        this.getCompanyList()
    }

    getCompanyList() {
        axios.get(`${config.api.vendor.company}/${this.props.match.params.guid}`)
        .then(res => {
            if (res.data.status === 200) {
                this.setState({
                    vendor: res.data.vendor,
                    companyList: res.data.companies
                })
            }
        })
    }

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

    selectCompany(e, company) {
        e.preventDefault()

        this.getLocationList()
        this.setState(prevState => {
            return {
                companyModalAction: 'update',
                selectedCompany: {
                    ...company,
                    company_logo_preview: company.company_logo || 'https://via.placeholder.com/150',
                },
                editableSelectedCompany: {
                    ...company,
                    company_logo_preview: company.company_logo || 'https://via.placeholder.com/150',
                },
                isCompanyModalOpen: true
            }
        })
    }

    createCompany() {
        const companyObj = {
            name: '',
            desc: '',
            address_line: '',
            postcode: '',
            region: '',
            state: '',
            company_logo: '',
            company_logo_preview: 'https://via.placeholder.com/150'
        }

        this.getLocationList()
        this.setState({
            isCompanyModalOpen: true,
            companyModalAction: 'create',
            selectedCompany: companyObj,
            editableSelectedCompany: companyObj
        })
    }

    saveCompany(e) {
        let formData = new FormData()
        formData.append('name', this.state.editableSelectedCompany.name)
        formData.append('desc', this.state.editableSelectedCompany.desc)
        formData.append('address_line', this.state.editableSelectedCompany.address_line)
        formData.append('postcode', this.state.editableSelectedCompany.postcode)
        formData.append('region', this.state.editableSelectedCompany.region)
        formData.append('state', this.state.editableSelectedCompany.state)
        formData.append('company_logo', this.state.editableSelectedCompany.company_logo)
        let url = '';

        if (this.state.companyModalAction === 'update') {
            url = `${config.api.vendor.company}/update/${this.state.editableSelectedCompany.id}`
        } else if (this.state.companyModalAction === 'create') {
            url = `${config.api.vendor.company}/${this.props.match.params.guid}`
        } else {
            swalErr({
                text: 'Somthing went wrong'
            }).then(() => {
                window.location.reload()
            })

            return false
        }

        axios.post(url, formData)
        .then(res => {
            if (res.data.status === 200) {
                swalSuccess({
                    text: res.data.message
                }).then(() => {
                    this.setState(prevState => {
                        return {
                            isCompanyModalOpen: false,
                            companyModalAction: '',
                            selectedCompany: {},
                            editableSelectedCompany: {}
                        }
                    })
                    this.getCompanyList()
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

    saveBank(e) {
        e.preventDefault()
        if (this.state.bankModalAction === 'create') {
            axios.post(`${config.api.vendor.bank}/${this.state.selectedCompany.id}`, {
                bank_name: this.state.editableSelectedBank.bank_name,
                bank_account: this.state.editableSelectedBank.bank_account,
                bank_account_name: this.state.editableSelectedBank.bank_account_name,
            })
            .then(res => {
                if (res.data.status === 200) {
                    swalSuccess({text: res.data.message})
                    this.setState(prevState => {
                        return {
                            isBankModalOpen: !prevState.isBankModalOpen
                        }
                    })
                    this.getCompanyList()
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
        } else if (this.state.bankModalAction === 'update') {
            axios.put(`${config.api.vendor.bank}/${this.state.selectedBank.id}`, {
                bank_name: this.state.editableSelectedBank.bank_name,
                bank_account: this.state.editableSelectedBank.bank_account,
                bank_account_name: this.state.editableSelectedBank.bank_account_name,
            })
            .then(res => {
                if (res.data.status === 200) {
                    swalSuccess({text: res.data.message})
                    this.setState(prevState => {
                        return {
                            isBankModalOpen: !prevState.isBankModalOpen
                        }
                    })
                    this.getCompanyList()
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
        } else {
            swalErr().then(() => {
                window.location.reload()
            })
        }
    }

    handleChange(e, type) {
        const property = e.target.id
        this.setState({
            [type]: {
                ...this.state[type],
                [property]: e.target.value
            }
        })
    }

    handleChangeSelect(selectOption, {name}) {
        this.setState(prevState => {
            return {
                editableSelectedCompany: {
                    ...prevState.editableSelectedCompany,
                    [name]: selectOption.value
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
                    editableSelectedCompany: {
                        ...prevState.editableSelectedCompany,
                        [property + '_preview']: reader.result
                    }
                }
            })
        }
        if (file) {
            reader.readAsDataURL(file)
            this.setState(prevState => {
                return {
                    editableSelectedCompany: {
                        ...prevState.editableSelectedCompany,
                        [property]: file
                    }
                }
            })
        }
    }

    clearModalData() {
        this.setState({
            selectedCompany: {},
            editableSelectedCompany: {}
        })
    }

    showCompanyBank(e, company) {
        e.preventDefault()
        const bank = company.bank

        this.setState(prevState => {
            return {
                selectedCompany: company,
                isBankModalOpen: !prevState.isBankModalOpen,
                selectedBank: bank ? bank : {},
                editableSelectedBank: bank ? bank : {},
                bankModalAction: 'update'
            }
        })
    }

    addCompanyBank(e, company) {
        e.preventDefault()

        this.setState(prevState => {
            return {
                selectedCompany: company,
                isBankModalOpen: !prevState.isBankModalOpen,
                editableSelectedBank: {
                    bank_name: '',
                    bank_account: '',
                    bank_account_name: '',
                },
                bankModalAction: 'create'
            }
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
        const companyList = this.state.companyList.map((company, index) => {
            return (
                <tr key={index}>
                    <td>{index + 1}</td>
                    <td>
                        <a href="#" onClick={(e) => this.selectCompany(e, company)}>
                            {company.name}
                        </a>
                    </td>
                    <td>
                        {
                            company.company_logo ?
                            (
                                <img width="150px" src={company.company_logo} className="rounded-circle" width="20" height="20" />
                            ) :
                            company.name
                        }
                    </td>
                    <td>{`${company.address_line}, ${company.postcode} ${company.region}, ${company.state}`}</td>
                    <td>{company.created_at}</td>
                    <td>
                        {
                            company.bank === null ?
                            (
                                <a href="#" className="btn btn-success btn-sm" onClick={(e) => this.addCompanyBank(e, company)}>
                                    Add
                                </a>
                            ) :
                            (
                                <a href="#" onClick={(e) => this.showCompanyBank(e, company)}>
                                    {company.bank.bank_name}
                                </a>
                            )
                        }
                    </td>
                    <td>
                        <Link to={`${config.paths.vendors.services}/${company.id}`}>
                            <i className="fas fa-broom"></i>
                        </Link>
                    </td>
                </tr>
            )
        })

        return (
            <div id="vendor-management">
                <h3>
                    <a href="#" className="btn btn-link" onClick={this.props.history.goBack}>
                        <i className="fas fa-chevron-left"></i>
                    </a>
                    <span className="ml-2">{this.state.vendor.name || ''}'s Companies</span>
                    <button onClick={this.createCompany} className="btn btn-success btn-sm ml-2">Add company</button>
                </h3>

                <div className="table-responsive">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Logo</th>
                                <th>Address</th>
                                <th>Created At</th>
                                <th>Bank</th>
                                <th>
                                    <i className="fas fa-cog"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {companyList.length > 0 ? companyList : (
                                <tr>
                                    <td colSpan="5">List is empty!</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Modal isOpen={this.state.isCompanyModalOpen} toggle={() => this.toggleModal('isCompanyModalOpen')} onClosed={this.clearModalData}>
                    <ModalHeader toggle={() => this.toggleModal('isCompanyModalOpen')}>Company {this.state.selectedCompany.name}</ModalHeader>
                    <ModalBody>
                        <div className="text-center">
                            <a href="#" onClick={() => this.imageInput.click()}>
                                <img src={this.state.editableSelectedCompany.company_logo_preview} className="rounded" />
                            </a>
                            <input id="company_logo" type="file" className={`d-none form-control`} onChange={this.handleFileInputChange} ref={(el) => this.imageInput = el}/>
                            <p><small>Image size: 150 x 150</small></p>
                        </div>
                        <div className="form-group">
                            <label>Name</label>
                            <input id="name" className={`form-control`} onChange={(e) => this.handleChange(e, 'editableSelectedCompany')} value={this.state.editableSelectedCompany.name} />
                        </div>
                        <div className="form-group">
                            <label>Description</label>
                            <input id="desc" className={`form-control`} onChange={(e) => this.handleChange(e, 'editableSelectedCompany')} value={this.state.editableSelectedCompany.desc} />
                        </div>
                        <div className="form-group">
                            <label>Address Line</label>
                            <input id="address_line" className={`form-control`} onChange={(e) => this.handleChange(e, 'editableSelectedCompany')} value={this.state.editableSelectedCompany.address_line} />
                        </div>
                        <div className="form-group">
                            <label>Postcode</label>
                            <input id="postcode" className={`form-control`} onChange={(e) => this.handleChange(e, 'editableSelectedCompany')} value={this.state.editableSelectedCompany.postcode} />
                        </div>
                        <div className="form-group">
                            <label>Region / City</label>
                            <Select
                                value={this.state.editableSelectedCompany.region ? {
                                    label: this.state.editableSelectedCompany.region,
                                    value: this.state.editableSelectedCompany.region
                                } : {}}
                                name="region"
                                options={this.state.regionList}
                                className="select"
                                onChange={this.handleChangeSelect}
                            />
                            {/* <input id="region" className={`form-control`} onChange={(e) => this.handleChange(e, 'editableSelectedCompany')} value={this.state.editableSelectedCompany.region} /> */}
                        </div>
                        <div className="form-group">
                            <label>State</label>
                            <Select
                                value={this.state.editableSelectedCompany.state ? {
                                    label: this.state.editableSelectedCompany.state,
                                    value: this.state.editableSelectedCompany.state
                                } : {}}
                                name="state"
                                options={this.state.stateList}
                                className="select"
                                onChange={this.handleChangeSelect}
                            />
                            {/* <input id="state" className={`form-control`} onChange={(e) => this.handleChange(e, 'editableSelectedCompany')} value={this.state.editableSelectedCompany.state} /> */}
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <button className="btn btn-primary" onClick={this.saveCompany}>Save</button>
                        <button className="btn btn-secondary" onClick={() => this.toggleModal('isCompanyModalOpen')}>Cancel</button>
                    </ModalFooter>
                </Modal>

                <Modal isOpen={this.state.isBankModalOpen} toggle={(e) => this.toggleModal('isBankModalOpen')} onClosed={this.clearModalData}>
                    <ModalHeader toggle={(e) => this.toggleModal('isBankModalOpen')}>Bank Info of {this.state.selectedCompany.name}</ModalHeader>
                    <ModalBody>
                        <div className="form-group">
                            <label>Bank Name</label>
                            <input id="bank_name" className={`form-control`} onChange={(e) => this.handleChange(e, 'editableSelectedBank')} value={this.state.editableSelectedBank.bank_name} />
                        </div>
                        <div className="form-group">
                            <label>Bank Account No</label>
                            <input id="bank_account" className={`form-control`} onChange={(e) => this.handleChange(e, 'editableSelectedBank')} value={this.state.editableSelectedBank.bank_account} />
                        </div>
                        <div className="form-group">
                            <label>Bank Account Name</label>
                            <input id="bank_account_name" className={`form-control`} onChange={(e) => this.handleChange(e, 'editableSelectedBank')} value={this.state.editableSelectedBank.bank_account_name} />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <button className="btn btn-primary" onClick={this.saveBank}>Save</button>
                        <button className="btn btn-secondary" onClick={(e) => this.toggleModal('isBankModalOpen')}>Cancel</button>
                    </ModalFooter>
                </Modal>
            </div>
        )
    }
}

export default VendorCompanyPage
