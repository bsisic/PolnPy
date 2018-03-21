import React, { Component } from 'react';
import './App.css';

class AllergicChecker extends Component {
    constructor(props){
        super(props);
        this.props = {
            nameAdded: ''
        }
    }
    render() {
    return (
          <div className="circle-container-check">
              <div className="Circle">
                  <div className="circle-content">
                      <h3 className="title-bulls">
                          Are you alergic to pollen {this.props.nameAdded} ?
                      </h3>
                      <button className="btn checker" type="button"><span>Yes</span></button>
                      <button className="btn checker" type="button"><span>No</span></button>
                  </div>
              </div>
          </div>
      );
    }
}

export default AllergicChecker;
