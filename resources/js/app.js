
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes React and other helpers. It's a great starting point while
 * building robust, powerful web applications using React + Laravel.
 */

import './bootstrap';

/**
 * Next, we will create a fresh React component instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import React from 'react';
import ReactDOM from 'react-dom';

// import App from './components/App';

import { BrowserRouter as Router, Switch } from 'react-router-dom'
import LoginPage from './components/login'
import AdminPage from './components/admin-panel'

import { PrivateRoute, GuestRoute } from './services/middlewares/authMiddleware'
import './services/middlewares/axiosMiddleware'
import config from './config'

if (document.getElementById('root')) {
    ReactDOM.render(
        <Router basename={config.publicBaseUrl}>
            <Switch>
                <GuestRoute exact path="/login" component={LoginPage} />
                <PrivateRoute path="/" component={AdminPage} />
            </Switch>
        </Router>,
        document.getElementById('root')
    )
}
