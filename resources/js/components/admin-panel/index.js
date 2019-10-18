import React from 'react'
import { Route, Switch, Redirect } from 'react-router-dom'
import DashboardPage from '../dashboard'
import UserPage from '../users'
import UserAddressPage from '../users/address'
import ZonePage from '../zones'
import PromotionPage from '../promotion'
import ExtraChargePage from '../extraCharge'
import SettingPage from '../setting'
import ServicePage from '../services'
import BookingListPage from '../bookings/list'
import BookingCreatePage from '../bookings/create'
import VendorManagementPage from '../vendor-management'
import VendorCompanyPage from '../vendor-management/company'
import VendorCleanerPage from '../vendor-management/cleaner'
import VendorServicePage from '../vendor-management/service'
import PageNotFound from '../page-handler/page-not-found'
import TopNav from './layouts/TopNav'
import SideNav from './layouts/SideNav'
import { paths } from '../../config'
// import RestaurantPage from '../restaurants'
// import AdminPage from '../admins'
// import MenuPage from '../menus'
// import EventPromoPage from '../event-promos'
// import SettingPage from '../settings'
// import NotificationPage from '../notifications'

class AdminPanel extends React.Component {
  constructor(props) {
    super(props)

    this.state = {
      isSidebarOpen: false
    }
  }

  toggleSideBar() {
    this.setState({
      isSidebarOpen: !this.state.isSidebarOpen
    })
  }

  render() {
    return (
      <div id="wrapper">
        <TopNav toggleSideBar={(e) => this.toggleSideBar(e)} />
        <SideNav className={this.state.isSidebarOpen ? 'active' : ''}/>
        <div id="content">
          <Switch>
            <Route exact path={paths.settings.extracharge} component={ExtraChargePage} />
            <Route exact path="/dashboard" component={DashboardPage} />
            <Route exact path={paths.vendors.base} component={VendorManagementPage} />
            <Route exact path={`${paths.vendors.company}/:guid`} component={VendorCompanyPage} />
            <Route exact path={`${paths.vendors.cleaner}/:guid`} component={VendorCleanerPage} />
            <Route exact path={`${paths.vendors.services}/:companyId`} component={VendorServicePage} />
            <Route exact path={paths.settings.users} component={UserPage} />
            <Route exact path={`${paths.settings.addresses}/:guid`} component={UserAddressPage} />
            <Route exact path={paths.settings.zones} component={ZonePage} />
            <Route exact path={paths.settings.promotion} component={PromotionPage} />
            <Route exact path={paths.settings.services} component={ServicePage} />
            <Route exact path={paths.settings.base} component={SettingPage} />
            <Route exact path={paths.bookings.list} component={BookingListPage} />
            <Route exact path={paths.bookings.create} component={BookingCreatePage} />
            
            <Redirect exact from="/" to="/dashboard" />
            <Route component={PageNotFound} />
          </Switch>
        </div>
      </div>
    )
  }
}

export default AdminPanel
