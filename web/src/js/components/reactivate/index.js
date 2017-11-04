import React from "react";
import { Link } from "react-router";

import ThankYou from "components/thank-you";

export default class Reactivate extends React.Component {
  constructor() {
    super();
  }

  componentDidMount() {
    this.reactivate();
  }

  reactivate() {
    const data = {
      id: this.props.params.id,
      code: this.props.params.code
    };
    fetch("/api/reactivate", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data)
    }).catch(err => {
        console.error(err);
        this.setState({ error: err.message ? err.message : err });
      });
  }

  render() {
    return <ThankYou type={this.props.params.type} />
  }
}
