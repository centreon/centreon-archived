import parse from 'html-react-parser';
import DOMPurify from 'dompurify';
import { makeStyles } from 'tss-react/mui';

import { Typography } from '@mui/material';

import truncate from '../../truncate';

type StylesProps = Pick<Props, 'bold'>;

const useStyles = makeStyles<StylesProps>()((theme, { bold }) => ({
  information: {
    fontWeight: bold ? 600 : 'unset',
  },
}));

interface Props {
  bold?: boolean;
  content?: string;
}

const OutputInformation = ({ content, bold = false }: Props): JSX.Element => {
  const { classes } = useStyles({ bold });

  return (
    <Typography className={classes.information} variant="body2">
      {parse(DOMPurify.sanitize(truncate(content)))}
    </Typography>
  );
};

export default OutputInformation;
