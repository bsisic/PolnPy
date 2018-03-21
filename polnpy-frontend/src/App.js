import React, { Component } from 'react';
import './App.css';
import AllergicChecker from './AllergicChecker.js';

class App extends Component {
    constructor(props){
        super(props);
        this.state = {
            name: '',
            isHidden: true
        }
        this.handleChange = this.handleChange.bind(this);
        this.allergicCheckerAppear = this.allergicCheckerAppear.bind(this);
    }
    handleChange(e) {
        this.setState({name: e.target.value});
    }
    allergicCheckerAppear() {
        this.setState({isHidden: !this.state.isHidden});
    }
    render() {
    return (
        <div className="App">
          <div className="circle-container">
              <div className="Circle">
                  <div className="circle-content">
                      <h3 className="title-bulls">
                          What's your name ?
                      </h3>
                      <input placeholder="name" value={this.state.name} onChange={this.handleChange}/>
                      <button className="btn" type="button" onClick={this.allergicCheckerAppear}><span>Enter</span></button>
                  </div>
              </div>
              {!this.state.isHidden && <AllergicChecker nameAdded={this.state.name}/>}
          </div>
        </div>
      );
    }
}

export default App;
