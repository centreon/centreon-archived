import React from 'react';
import Select from 'react-select';

class MultiSelect extends React.Component {
  state = {
    selectedOption: null,
  }
  handleChange = (selectedOption) => {
    this.setState({ selectedOption });
  }
  render() {
    const { selectedOption } = this.state;
    const {options} = this.props;
    return (
      <Select
        value={selectedOption}
        onChange={this.handleChange}
        options={options}
        isMulti={true}
      />
    );
  }
}

export default MultiSelect;