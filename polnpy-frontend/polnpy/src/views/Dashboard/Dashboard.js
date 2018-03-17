import React, { Component } from 'react';
import {
    XYPlot,
    XAxis,
    YAxis,
    VerticalGridLines,
    HorizontalGridLines,
    VerticalBarSeries,
    VerticalBarSeriesCanvas
} from 'react-vis';
import Calendar from 'react-calendar';
import ImageFilter from 'react-image-filter';

class Dashboard extends Component {
    constructor() {
        super();
        this.state = {
            value: false,
            date: new Date(),
            histo_pollen: {},
            predict: {}
        }
        this.getHisto = this.getHisto.bind(this);
        this.getPredict = this.getPredict.bind(this);
    }
    componentWillMount(){
        this.getHisto();
        this.getPredict();
    }
    getHisto(){
        fetch('http://82.223.27.78:81/history?type=5aaca323861fc900151caba2&start=2018-03-14&end=2018-03-15')
        .then((res)=>{
            return res.json()
        })
        .then((res) => {
            this.setState({histo_pollen:res})
            console.log(this.state.histo_pollen)
        })
    }
    getPredict(){
        fetch('http://82.223.27.78:81/predict?pollen=5aaca323861fc900151caba2')
        .then((res)=>{
            return res.json()
        })
        .then((res) => {
            this.setState({predict:res})
            console.log(this.state.predict)
        })
    }
    render() {
        const {value} = this.state;
        return (
        <div className="container">
            <div className="row">
                <div className="col">
                    <Calendar
                      onChange={this.onChange}
                      value={this.state.date}
                    />
                </div>
                <div className="col histo">
                <h5 style={{display:'inline-block'}}>History&</h5><h5 style={{color:'#F5A623',display:'inline-block'}}>Prediction</h5>
                    <XYPlot
                      xType="ordinal"
                      width={500}
                      height={300}
                      xDistance={100}
                      >
                      <VerticalGridLines />
                      <HorizontalGridLines />
                      <XAxis />
                      <YAxis />
                      <VerticalBarSeriesCanvas
                        className="vertical-bar-series-example"
                        data={[
                          {x: 'Betula', y: 5}
                        ]}
                        color="#141414"/>
                      <VerticalBarSeriesCanvas
                        data={[
                            {x: 'Betula', y: this.state.predict.concentration}
                        ]}
                        color="#F5A623"/>
                    </XYPlot>
                </div>
                <div className="row">
                    <div className="col">
                    <div className="card map-card">
                    <ImageFilter
                        className="map-lx"
                        image='../img/map.svg'
                        filter={ 'duotone' }
                        colorOne={ [40, 250, 250] }
                        colorTwo={ [250, 150, 30] }
                      />
                      <div className="card-body">
                        <h4 className="card-title">{this.state.predict.pollen} Map</h4>
                        <p style={{display:'inline-block',marginLeft:'10px',color:'green'}}>normal</p>
                        <p style={{display:'inline-block',marginLeft:'10px',color:'orange'}}>warning</p>
                        <p style={{display:'inline-block',marginLeft:'10px',color:'red'}}>alert</p>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
        </div>
        );
     }
}

export default Dashboard;
