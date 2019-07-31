/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';
import Select from 'react-select';

class MultiSelect extends React.Component {
  state = {
    selectedOption: null,
  };

  handleChange = (selectedOption) => {
    this.setState({ selectedOption });
  };

  render() {
    const { selectedOption } = this.state;
    const { options, label } = this.props;
    return (
      <Select
        label={label}
        value={selectedOption}
        onChange={this.handleChange}
        options={options}
        isMulti
      />
    );
  }
}

export default MultiSelect;
