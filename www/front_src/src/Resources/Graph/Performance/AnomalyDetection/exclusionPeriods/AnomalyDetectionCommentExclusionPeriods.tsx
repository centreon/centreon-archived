import { useState } from 'react';

import { makeStyles } from 'tss-react/mui';

import { TextField, Typography } from '@mui/material';

const useStyles = makeStyles()((theme) => ({
  container: {
    margin: theme.spacing(2, 0),
  },
  field: {
    width: '100%',
  },
}));

const AnomalyDetectionCommentExclusionPeriod = (): JSX.Element => {
  const { classes } = useStyles();
  const [comment, setComment] = useState(null);

  const changeComment = (event): void => {
    console.log(event);
  };

  return (
    <div className={classes.container}>
      <Typography>Comment </Typography>
      <TextField
        multiline
        className={classes.field}
        rows={3}
        value={comment}
        variant="filled"
        onChange={changeComment}
      />
    </div>
  );
};

export default AnomalyDetectionCommentExclusionPeriod;
