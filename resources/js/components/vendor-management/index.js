import React from 'react'
import { Link } from 'react-router-dom'
import axios from 'axios'
import config from '../../config'

class VendorManagementPage extends React.Component {
    constructor() {
        super()

        this.state = {
            vendorList: [],
            companyList: [],
        }

        this.getVendorList = this.getVendorList.bind(this)
    }

    componentDidMount() {
        this.getVendorList()
    }

    getVendorList() {
        axios.get(config.api.users.base, {
            params: {
                role: 2
            }
        }).then(res => {
            if (res.data.status === 200) {
                this.setState({
                    vendorList: res.data.users
                })
            }
        })
    }

    render() {
        const vendorList = this.state.vendorList.map((vendor, index) => {
            return (
                <tr key={index}>
                    <td>{index + 1}</td>
                    <td>{vendor.email}</td>
                    <td>
                        {vendor.name}
                    </td>
                    <td>{vendor.updated_at}</td>
                    <td>{vendor.created_at}</td>
                    <td>
                        <Link to={`${config.paths.vendors.company}/${vendor.guid}`}>
                            <i className="far fa-building"></i>
                        </Link>
                        <Link className="ml-2" to={`${config.paths.vendors.cleaner}/${vendor.guid}`}>
                            <i className="fas fa-people-carry"></i>
                        </Link>
                        {/* <Link className="ml-2" to={`${config.paths.vendors.services}/${vendor.guid}`}>
                            <i className="fas fa-broom"></i>
                        </Link> */}
                        {/* <Link className="ml-2" to={`${config.paths.vendors.zones}/${vendor.guid}`}>
                            <i className="fas fa-map-marker-alt"></i>
                        </Link> */}
                    </td>
                </tr>
            )
        })

        return (
            <div id="vendor-management">
                <h3>
                    <span className="ml-2">Vendor Management</span>
                </h3>

                <div className="table-responsive">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Last Login</th>
                                <th>Joined At</th>
                                <th>
                                    <i className="fas fa-cog"></i>
                                    
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {vendorList.length > 0 ? vendorList : (
                                <tr>
                                    <td colSpan="5">List is empty!</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        )
    }
}

export default VendorManagementPage
