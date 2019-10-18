import React from 'react'
import swal from 'sweetalert2'
import { Link, withRouter } from 'react-router-dom'
import Cookies from 'js-cookie'
import axios from 'axios'
// import ChangePasswordModal from './components/ChangePasswordModal'
import config from '../../../config'

class TopNav extends React.Component {
  constructor(props) {
    super(props)

    this.state = {
      modalShow: false,
    }
  }

  toggleModal() {
    this.setState({
      modalShow: !this.state.modalShow
    })
  }

  logout() {
        swal({
            title: 'Logging out?',
            text: "Your session will be terminated.",
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Logout',
        })
        .then(result => {
            if (result.value) {
                axios.get(config.api.logout)
                .then(res => {
                    if (res.data.status === 200) {
                        Cookies.remove(config.cookieTokenName, {path: '/'})
                        window.location.href = `${config.publicBaseUrl}/login`
                    } else {
                        console.error('Logout error')
                    }
                })
            }
        })
  }

    render() {
        return (
            <nav id="nav-top" className="navbar fixed-top">
                {/* <ChangePasswordModal
                    modalShow={this.state.modalShow}
                    toggleModal={this.toggleModal}
                /> */}
                <Link to="/dashboard" className="navbar-brand">
                    <i className="fas fa-home fa-fw"></i>
                </Link>
                <button className="btn btn-sm btn-light" onClick={this.props.toggleSideBar}>
                    <i className="fas fa-bars"></i>
                </button>
                <form className="form-inline">
                    <button onClick={(e) => this.toggleModal(e)} className="btn btn-light btn-sm align-middle mx-2" type="button">
                        <i className="fas fa-fw fa-lock"></i> Change password
                    </button>
                    <button onClick={(e) => this.logout(e)} className="btn btn-light btn-sm align-middle mx-2" type="button">
                        <i className="fas fa-fw fa-power-off"></i> Logout
                    </button>
                </form>
            </nav>
        )
    }

}

export default withRouter(TopNav)
