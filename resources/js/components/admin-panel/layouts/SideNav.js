import React from 'react'
import PropTypes from 'prop-types'
import { Route, Link } from 'react-router-dom'
import config from '../../../config'
import { isObject } from '../../../services/helper/utilities'


class SideNav extends React.Component {
    constructor(props) {
        super(props)

        this.state = {
            openingList: ''
        }
    }

    toggleOpeningList(type, e) {
        e.preventDefault()
        this.setState(prevState => {
            return {
                openingList: prevState.openingList === type ? '' : type
            }
        })
    }

    render() {
        return (
            <nav id="sidenav" className={this.props.className}>
                <ul className="list-unstyled components">
                    <Route path={config.paths.vendors.base} children={(args) => {
                        return (
                            <li className={args.match ? 'active' : null}>
                                <Link to={config.paths.vendors.base}>
                                    <i className="fas fa-users fa-fw fa-sm"></i> Vendor Management
                                </Link>
                            </li>
                            
                        )
                    }}/>
                    

                    <Route path={config.paths.settings.base} children={(args) => {
                        return (
                            <li className={args.match ? 'active' : null}>
                                <a href="#" onClick={(e) => this.toggleOpeningList('settings', e)}>
                                    Settings <i className={`float-right fas fa-chevron-left fa-sm ${this.state.openingList === 'settings' ? 'fa-rotate-270' : ''}`}></i>
                                </a>
                                <ul className={`list-unstyled list-view ${this.state.openingList === 'settings' ? 'open' : ''}`}>
                                    <Route path={config.paths.settings.users} children={(args) => {
                                        return (
                                            <li className={args.match ? 'active' : null}>
                                                <Link to={config.paths.settings.users}>
                                                    <i className="fas fa-users fa-fw fa-sm"></i> Users Management
                                                </Link>
                                            </li>
                                        )
                                    }}/>
                                    {/* <Route path={config.paths.settings.zones} children={(args) => {
                                        return (
                                            <li className={args.match ? 'active' : null}>
                                                <Link to={config.paths.settings.zones}>
                                                    <i className="fas fa-map-marker-alt fa-fw fa-sm"></i> Zones
                                                </Link>
                                            </li>
                                        )
                                    }}/> */}
                                    <Route path={config.paths.settings.services} children={(args) => {
                                        return (
                                            <li className={args.match ? 'active' : null}>
                                                <Link to={config.paths.settings.services}>
                                                    <i className="fas fa-broom fa-fw fa-sm"></i> Services
                                                </Link>
                                            </li>
                                        )
                                    }}/>
                                </ul>
                            </li>
                        )
                    }}/>

                    <Route path={config.paths.bookings.base} children={(args) => {
                        return (
                            <li className={args.match ? 'active' : null}>
                                <a href="#" onClick={(e) => this.toggleOpeningList('bookings', e)}>
                                    Bookings <i className={`float-right fas fa-chevron-left fa-sm ${this.state.openingList === 'bookings' ? 'fa-rotate-270' : ''}`}></i>
                                </a>
                                <ul className={`list-unstyled list-view ${this.state.openingList === 'bookings' ? 'open' : ''}`}>
                                    <Route path={config.paths.bookings.list} children={(args) => {
                                        return (
                                            <li className={args.match ? 'active' : null}>
                                                <Link to={config.paths.bookings.list}>
                                                    <i className="fas fa-users fa-fw fa-sm"></i> List
                                                </Link>
                                            </li>
                                        )
                                    }}/>
                                    {/* <Route path={config.paths.bookings.create} children={(args) => {
                                        return (
                                            <li className={args.match ? 'active' : null}>
                                                <Link to={config.paths.bookings.create}>
                                                    <i className="fas fa-map-marker-alt fa-fw fa-sm"></i> Create
                                                </Link>
                                            </li>
                                        )
                                    }}/> */}
                                </ul>
                            </li>
                        )
                    }}/>


                   

                    <Route path={config.api.zone} children={(args) => {
                        return (
                            <li className={args.match ? 'active' : null}>
                                <Link to={config.paths.settings.zones}>
                                <i class="fas fa-street-view"></i>&nbsp; Zones
                                </Link>
                            </li>
                            
                        )
                    }}/>

                    <Route path={config.paths.settings.extracharge} children={(args) => {
                        return (
                            <li className={args.match ? 'active' : null}>
                                <Link to={config.paths.settings.extracharge}>
                                <i class="fas fa-coins"></i>&nbsp; Extra Charge
                                </Link>
                            </li>
                            
                        )
                    }}/>

                    <Route path={config.api.promotion} children={(args) => {
                        return (
                            <li className={args.match ? 'active' : null}>
                                <Link to={config.paths.settings.promotion}>
                                <i class="fas fa-street-view"></i>&nbsp; Promotion
                                </Link>
                            </li>
                            
                        )
                    }}/>

                    

                    <Route path={config.paths.settings.base} children={(args) => {
                        return (
                            <li className={args.match ? 'active' : null}>
                                <Link to={config.paths.settings.base}>
                                <i class="fas fa-street-view"></i>&nbsp; Setting
                                </Link>
                            </li>
                            
                        )
                    }}/>
                </ul>
            </nav>
        )
    }
}

// const SideNav = (props) => {
//     return (
//         <nav id="sidenav" className={props.className}>
//             <ul className="list-unstyled components">

//                 <Route path="/users" children={(args) => {
//                     return (
//                         <li className={args.match ? 'active' : null}>
//                             <a href="#">
//                                 Users Management
//                             </a>
//                             <ul className={`list-unstyled list-view ${}`}>
//                                 <li>
//                                     <Link to="/users">
//                                         <i className="far fa-user fa-fw"></i> Vendors
//                                     </Link>
//                                 </li>
//                                 <li>
//                                     <Link to="/users">
//                                         <i className="far fa-user fa-fw"></i> Users
//                                     </Link>
//                                 </li>
//                             </ul>
//                         </li>
//                     )
//                 }}/>

//                 <NavItem to="/users">
//                     <i className="far fa-user fa-fw"></i> Users
//                 </NavItem>

//                 <NavItem to='/admin/restaurants'>
//                 <i className="fa fa-home fa-fw"></i> Restaurants
//                 </NavItem>
//                 <NavItem to="/admin/news">
//                 <i className="fa fa-newspaper-o fa-fw"></i> News
//                 </NavItem>
//                 <NavItem to={{
//                 pathname: '/admin/menus',
//                 search: `?status=${config.defaultStatus}`,
//                 }}>
//                 <i className="fa fa-cutlery fa-fw"></i> Menus
//                 </NavItem>
//                 <NavItem to="/admin/settings">
//                 <i className="fa fa-cogs fa-fw"></i> Settings
//                 </NavItem>
//                 <NavItem to="/admin/notifications">
//                 <i className="fa fa-bell-o fa-fw"></i> Notifications
//                 </NavItem>
//             </ul>
//         </nav>
//     );
// };

const NavItem = (props) => (
  <Route path={isObject(props.to) ? props.to.pathname : props.to} children={(args) => {
    return (
      <li className={args.match ? 'active' : null}>
        <Link to={props.to}>{props.children}</Link>
      </li>
    )
  }}/>
)

NavItem.prototype = {
  to: PropTypes.string.isRequired,
  children: PropTypes.node.isRequired
}

export default SideNav;
