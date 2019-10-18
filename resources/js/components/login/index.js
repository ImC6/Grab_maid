import React from 'react'
import axios from 'axios'
import Cookies from 'js-cookie'
import { Alert } from 'reactstrap'
// import LogoImage from '../../images/logo.jpg'
import config from '../../config'
import { swalErr } from '../../services/helper/utilities'
// import './styles/login.scss'

const defaultErrMsg = {
    email: '',
    password: '',
    alert: '',
}

class LoginPage extends React.Component {
    constructor(props) {
        super(props)

        this.state = {
            email: '',
            password: '',
            errMsg: { ...defaultErrMsg },
            isLoading: false,
        }
    }

    handleChange(e) {
        let property = e.target.id
        this.setState({
            [property]: e.target.value
        });
    }

    handleEnter(e) {
        if (e.key === 'Enter') {
            this.login()
        }
    }

    validateField() {
        let errMsg = { ...defaultErrMsg }
        let hasError = false
        if (this.state.email === '') {
            errMsg.email = 'Email must not be empty'
            hasError = true
        }
        if (this.state.password === '') {
            errMsg.password = 'Password must not be empty'
            hasError = true
        }
        this.setState({
            errMsg
        })
        return !hasError
    }

    login() {
        if (!this.validateField()) {
            return false;
        }

        this.setState({ isLoading: true })

        axios.post(config.api.admin.login, {
            email: this.state.email,
            password: this.state.password
        })
        .then(res => {
            this.setState({ isLoading: false });

            if (res.data.status === 200) {
                Cookies.set(config.cookieTokenName, res.data.token, {
                    // expires: config.cookieExpirationDay
                })
                window.location.href = `${config.publicBaseUrl}`

            } else if (res.data.status === 400) {
                this.setState({
                    errMsg: {
                        ...this.state.errMsg,
                        alert: res.data.errors
                    }
                })
            } else {
                swalErr({
                    text: "Login error! Try refreshing page"
                })
                .then(res => {
                    window.location.reload()
                })
            }
        })
    }

    render() {
        return (
            <div className="container">
                <div className="row">
                    <div className="col-sm-6 offset-sm-3">
                        <h1 className="text-center">Grabmaid Admin</h1>
                        <div className="card">
                            <div className="card-body" onKeyPress={(e) => this.handleEnter(e)}>
                                <div className="form-group">
                                    <label>Email</label>
                                    <input type="text" id="email" className={`form-control ${this.state.errMsg.email !== '' ? 'is-invalid' : ''}`} onChange={(e) => this.handleChange(e)} name="email" />
                                    <div className="invalid-feedback">{this.state.errMsg.email}</div>
                                </div>
                                <div className="form-group">
                                    <label>Password</label>
                                    <input type="password" id="password" className={`form-control ${this.state.errMsg.email !== '' ? 'is-invalid' : ''}`} onChange={(e) => this.handleChange(e)} name="password" />
                                    <div className="invalid-feedback">{this.state.errMsg.password}</div>
                                </div>
                                <Alert color="danger" isOpen={this.state.errMsg.alert !== ''}>
                                    {this.state.errMsg.alert}
                                </Alert>
                                <button type="button" className="btn btn-info col-12" onClick={(e) => this.login(e)}>Login</button>
                            </div>
                        </div>
                        <div className={`loader ${this.state.isLoading ? 'show' : ''}`}>
                            <i className="fa fa-circle-o-notch fa-spin fa-3x text-white"></i>
                        </div>
                    </div>
                </div>

            </div>
        );
    }
}

export default LoginPage;
