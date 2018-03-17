import React, { Component } from 'react';
import axios from 'axios';

class TypeOfPollen extends Component {
    constructor() {
        super();
        this.state = {
            types_pollen: []
        }
        this.checkPredict = this.checkPredict.bind(this)
    }
    componentWillMount(){
    fetch('http://82.223.27.78:81/list')
        .then((res)=>{
            return res.json()
        })
        .then((res) => {
            this.setState({types_pollen:res})
            console.log(this.state.types_pollen)
        })
    }
    checkPredict(pred){
        if(pred == true){
            return 'Yes'
        } else {
            return 'No'
        }
    }
    render() {
        return (
            <div className="container">
                <div className="row">
                    {this.state.types_pollen.map((type_pollen, i) => {
                      return <div className="col type-pollen-container" key={i}>
                        <div className="card type-pollen-card">
                          <img className="card-img-top type-pollen-img" src={type_pollen.image} alt="Card image" />
                          <div className="card-body">
                            <h4 className="card-title">{type_pollen.name}</h4>
                            <p className="card-text">Predictive : {this.checkPredict(type_pollen.isPredictive)}</p>
                            <a href="#" className="btn btn-primary">Check data about {type_pollen.name}</a>
                          </div>
                        </div></div>
                    })}
                </div>
            </div>
            );
          }
}

export default TypeOfPollen;
