import React, { Component } from 'react';

class TopupPage extends Component {
    constructor(props) {
        super(props)

        this.state = {

        }
    }

    render() {
        return (
            <div id="topup-list">
                <h3>
                    Top Up Settings

                    <button onClick={this.addService} className="btn btn-success btn-sm ml-2">Add topup</button>
                </h3>



            </div>
        )
    }
}

export default TopupPage
