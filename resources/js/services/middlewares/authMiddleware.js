import React from 'react'
import { Route, Redirect } from 'react-router-dom'
import auth from '../auth'

export const PrivateRoute = ({ component: Component, ...rest }) => (
  <Route {...rest} render={props => (
    auth.check() ? (
      <Component {...props} />
    ) : (
        <Redirect to={{
          pathname: '/login',
          state: { from: props.location }
        }} />
      )
  )} />
)

export const GuestRoute = ({ component: Component, ...rest }) => (
  <Route {...rest} render={props => (
    !auth.check() ? (
      <Component {...props} />
    ) : (
        <Redirect to={{
          pathname: '/dashboard',
          state: { from: props.location }
        }} />
      )
  )} />
)

