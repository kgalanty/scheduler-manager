export default class LeaveRequest
{
    constructor(type)
    {
        this.type = type
    }

    isVacationRequest()
    {
        return this.type === 1
    }

    isSickLeaveRequest()
    {
        return this.type === 2
    }
    
    isShiftChangeRequest()
    {
        return this.type === 3
    }

}