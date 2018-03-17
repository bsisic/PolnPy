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

class Dashboard extends Component {
    constructor() {
        super();
        this.state = {
            value: false,
            date: new Date()
        }
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
                <div className="col">
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
                          {x: 'A', y: 10},
                          {x: 'B', y: 5},
                          {x: 'C', y: 15},
                          {x: 'D', y: 8},
                          {x: 'E', y: 2},
                          {x: 'F', y: 6}
                        ]}
                        color="#141414"/>
                      <VerticalBarSeriesCanvas
                        data={[
                          {x: 'A', y: 12},
                          {x: 'B', y: 2},
                          {x: 'C', y: 11},
                          {x: 'D', y: 18},
                          {x: 'E', y: 2},
                          {x: 'F', y: 32}
                        ]}
                        color="#F5A623"/>
                    </XYPlot>
                </div>
                <div className="row">
                    <div className="col">
                        <img src="../img/map.svg" width="500" height="300"/>
                    </div>
                </div>
            </div>
        </div>
        );
      }
}

export default Dashboard;
