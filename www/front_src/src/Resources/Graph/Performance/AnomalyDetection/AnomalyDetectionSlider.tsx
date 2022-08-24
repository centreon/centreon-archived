import { useState } from 'react';

import AddIcon from '@mui/icons-material/Add';
import RemoveIcon from '@mui/icons-material/Remove';
import Slider from '@mui/material/Slider';
import makeStyles from '@mui/styles/makeStyles';

import { IconButton } from '@centreon/ui';

const useStyles = makeStyles((theme) => ({
  container: {
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'space-evenly',
  },
  slider: {
    '& .MuiSlider-thumb': {
      height: theme.spacing(3),
      width: 1,
    },
    '& .MuiSlider-valueLabel': {
      backgroundColor: theme.palette.primary.main,
      borderRadius: '50%',
    },
    '& .MuiSlider-valueLabel:before': {
      width: 0,
    },
    '& .MuiSlider-valueLabelOpen': {
      transform: 'translateY(-60%) scale(1)',
    },
    display: 'flex',
    justifyContent: 'space-evenly',
    width: theme.spacing(35),
  },
  sliderContainer: {
    alignItems: 'center',
    display: 'flex',
  },
}));

const AnomalyDetectionSlider = (): JSX.Element => {
  const classes = useStyles();
  const dataSlider = {
    currentValue: 0.8,
    defaultValue: 2,
  };
  const step = 0.1;
  const [currentValue, setCurrentValue] = useState(dataSlider.currentValue);

  const handleChangeSlider = (value): void => {
    setCurrentValue(value);
  };

  const handleAdd = (): void => {
    const newCurrentValue = Number((step + currentValue).toFixed(1));
    setCurrentValue(newCurrentValue);
  };

  const handleRemove = (): void => {
    const newCurrentValue = Number((currentValue - step).toFixed(1));
    setCurrentValue(newCurrentValue);
  };

  return (
    <div className={classes.container}>
      <div>Title1</div>
      <div>Title2</div>

      <div className={classes.sliderContainer}>
        <IconButton data-testid="add" size="small" onClick={handleRemove}>
          <RemoveIcon fontSize="small" />
        </IconButton>

        <Slider
          aria-label="Small"
          className={classes.slider}
          max={5}
          min={0}
          size="small"
          step={step}
          value={currentValue}
          valueLabelDisplay="on"
          onChange={handleChangeSlider}
        />
        <IconButton data-testid="remove" size="small" onClick={handleAdd}>
          <AddIcon fontSize="small" />
        </IconButton>
      </div>
    </div>
  );
};

export default AnomalyDetectionSlider;
