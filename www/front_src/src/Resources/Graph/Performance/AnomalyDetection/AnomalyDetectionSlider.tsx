import { useState, useEffect } from 'react';

import { equals } from 'ramda';

import Typography from '@mui/material/Typography';
import AddIcon from '@mui/icons-material/Add';
import RemoveIcon from '@mui/icons-material/Remove';
import Slider from '@mui/material/Slider';
import makeStyles from '@mui/styles/makeStyles';
import FormControlLabel from '@mui/material/FormControlLabel';
import Checkbox from '@mui/material/Checkbox';
import Button from '@mui/material/Button';

import { IconButton } from '@centreon/ui';

import { CustomFactorsData } from './models';

const useStyles = makeStyles((theme) => ({
  body: {
    display: 'flex',
    flexDirection: 'column',
  },
  bodyContainer: {
    alignItems: 'center',
    display: 'flex',
    marginBottom: theme.spacing(2),
    marginTop: theme.spacing(5),
  },
  confirmButton: {
    marginLeft: theme.spacing(2),
  },
  container: {
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'space-evenly',
    padding: theme.spacing(2),
  },
  footer: {
    display: 'flex',
    justifyContent: 'flex-end',
  },
  header: {
    display: 'flex',
    flexDirection: 'column',
  },
  icon: {
    display: 'flex',
    flexDirection: 'column',
  },
  slider: {
    '& .MuiSlider-mark': {
      borderLeft: '1px solid',
      height: theme.spacing(2),
      width: 0,
    },
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
}));

interface Props {
  getFactors: (data: CustomFactorsData) => void;
}

const AnomalyDetectionSlider = ({ getFactors }: Props): JSX.Element => {
  const classes = useStyles();
  const dataSlider = {
    currentValue: 0.8,
    defaultValue: 2,
  };
  const maxSlider = 5;
  const minSlider = 0;
  const step = 0.1;
  const [currentValue, setCurrentValue] = useState(dataSlider.currentValue);
  const [isDefaultValue, setIsDefaultValue] = useState(false);
  const [isResizing, setIsResizing] = useState(false);

  const marks = [
    {
      label: 'Default',
      value: dataSlider.defaultValue,
    },
  ];

  const handleChangeSlider = (event): void => {
    setCurrentValue(event.target.value);
    setIsDefaultValue(false);
    setIsResizing(true);
  };

  const handleAdd = (): void => {
    const newCurrentValue = Number((step + currentValue).toFixed(1));
    setCurrentValue(newCurrentValue);
    setIsDefaultValue(false);
    setIsResizing(true);
  };

  const handleRemove = (): void => {
    const newCurrentValue = Number((currentValue - step).toFixed(1));
    setCurrentValue(newCurrentValue);
    setIsDefaultValue(false);
    setIsResizing(true);
  };

  const handleChangeCheckBox = (event): void => {
    setIsResizing(true);
    if (isDefaultValue) {
      return;
    }
    setIsDefaultValue(event?.target.checked);
  };

  const resizeEnvelope = (): void => {
    console.log('confirm');
  };

  const cancelResizingEnvelope = (): void => {
    console.log('cancel');
  };
  useEffect(() => {
    if (isDefaultValue) {
      setCurrentValue(dataSlider.defaultValue);
    }
  }, [isDefaultValue]);

  useEffect(() => {
    if (equals(currentValue, dataSlider.defaultValue)) {
      setIsDefaultValue(true);
    }

    getFactors({
      currentFactor: dataSlider.currentValue,
      isResizing,
      simulatedFactor: currentValue,
    });
  }, [currentValue]);

  return (
    <div className={classes.container}>
      <div className={classes.header}>
        <Typography variant="h6">Manage envelop size</Typography>
        <Typography variant="caption">
          Changes to the envelope size will be applied immediately
        </Typography>
      </div>

      <div className={classes.body}>
        <div className={classes.bodyContainer}>
          <IconButton data-testid="add" size="small" onClick={handleRemove}>
            <div className={classes.icon}>
              <RemoveIcon fontSize="small" />
              <Typography variant="subtitle2">{minSlider}</Typography>
            </div>
          </IconButton>

          <Slider
            aria-label="Small"
            className={classes.slider}
            marks={marks}
            max={maxSlider}
            min={minSlider}
            size="small"
            step={step}
            value={currentValue}
            valueLabelDisplay="on"
            onChange={handleChangeSlider}
          />
          <IconButton data-testid="remove" size="small" onClick={handleAdd}>
            <div className={classes.icon}>
              <AddIcon fontSize="small" />
              <Typography variant="subtitle2">{maxSlider}</Typography>
            </div>
          </IconButton>
        </div>
        <FormControlLabel
          control={
            <Checkbox
              checked={isDefaultValue}
              onChange={handleChangeCheckBox}
            />
          }
          label="use default value"
        />
      </div>

      <div className={classes.footer}>
        <Button size="small" variant="text" onClick={cancelResizingEnvelope}>
          Cancel
        </Button>
        <Button
          className={classes.confirmButton}
          size="small"
          variant="contained"
          onClick={resizeEnvelope}
        >
          Confirm
        </Button>
      </div>
    </div>
  );
};

export default AnomalyDetectionSlider;
